# YAY Partner SDK (PHP)

[![Latest Version](https://img.shields.io/packagist/v/yay-photobooks/partner-sdk.svg)](https://packagist.org/packages/yay-photobooks/partner-sdk)
[![License](https://img.shields.io/packagist/l/yay-photobooks/partner-sdk.svg)](https://packagist.org/packages/yay-photobooks/partner-sdk)
[![PHP Version](https://img.shields.io/packagist/php-v/yay-photobooks/partner-sdk.svg)](https://packagist.org/packages/yay-photobooks/partner-sdk)

A simple, type-safe PHP SDK for integrating with the YAY Photobook Partner API. Enable YAY to create beautiful photobooks for your customers.

## Not using PHP?

We use a REST API that you can query with any language that supports HTTP requests.

The API specification is defined in [`openapi-partner-api.yaml`](openapi-partner-api.yaml) (OpenAPI 3.1).


## Features

- 🔒 **Type-safe DTOs** - Catch API changes at compile time
- 🌍 **Environment support** - Easy switching between sandbox and production
- 🐛 **Debug-friendly** - Access to original HTTP request/response data
- 📋 **RFC 7807 compliant** - Standardized error handling
- ⚡ **Simple setup** - Environment variable configuration with direnv support

## Installation

```bash
composer require yay-photobooks/partner-sdk
```

## Quick Start

### 1. Environment Setup

Set the variables as shown in the .envrc.example.

When you use direnv, its as simple as copying and modifiny .envrc.example

```bash
# Install direnv: https://direnv.net/docs/installation.html
cp .envrc.example .envrc
direnv allow
```

### 2. Create a Photobook Project

```php
<?php

require_once 'vendor/autoload.php';

use YAY\PartnerSDK\Client;
use YAY\PartnerSDK\Dto\V1\CreateProjectRequest;
use YAY\PartnerSDK\Dto\V1\Customer;
use YAY\PartnerSDK\Dto\V1\Address;
use YAY\PartnerSDK\Dto\V1\Upload;

$client = new Client();

$project = new CreateProjectRequest(
    title: "Sarah & Mike's Wedding Album",
    customer: new Customer(
        firstname: "Sarah",
        lastname: "Mueller",
        email: "sarah.mueller@gmail.com",
        phone: "+4917612345678",
        address: new Address(
            line1: "Musterstraße 123",
            line2: "Apartment 4B",
            city: "Berlin",
            postalCode: "10115",
            country: "DE"
        )
    ),
    upload: new Upload(
        numberOfImages: 150,
        coverUrl: "https://my-photo-app.example.com/images/wedding-cover.jpg",
        photoUrls: [
            "https://my-photo-app.example.com/photos/img001.jpg",
            "https://my-photo-app.example.com/photos/img002.jpg",
            // ... more photo URLs
        ]
    ),
    locale: "de_DE"
);

$result = $client->createProject($project);
```

### B2B preview-before-checkout (no address)

Partners can create a project before collecting the customer's postal address — the customer
fills it in during checkout in YAY's own form. See `examples/create-project-no-address.php`.

```php
$project = new CreateProjectRequest(
    title: "Sarah & Mike's Wedding Album",
    customer: new Customer(
        firstname: "Sarah",
        lastname: "Mueller",
        email: "sarah.mueller@gmail.com",
        // no address — collected at checkout
    ),
    upload: new Upload(
        numberOfImages: 150,
        coverUrl: "https://my-photo-app.example.com/images/wedding-cover.jpg",
    ),
    locale: "de_DE"
);
```

```php
if ($result->isSuccess()) {
    $response = $result->getResult();
    echo "✅ Project created successfully!\n";
    echo "Project ID: " . $response->projectId . "\n";
    echo "Redirect your customer to: " . $response->redirectUrl . "\n";
} else {
    $error = $result->getError();
    echo "❌ Error: " . $error->title . "\n";
    echo "Details: " . $error->detail . "\n";
    
    // Debug information
    echo "HTTP Status: " . $result->getResponse()->getStatusCode() . "\n";
}
```

## Webhooks

When the status of a project changes, we send a `POST` request to your webhook URL.
The SDK does not receive webhooks: handle them in your own application.

**Setup:** log in to the partner area and open [`/admin/webhooks`](https://portal.yayphotobooks.com/admin/webhooks) (sandbox: [`/admin/webhooks`](https://sandbox.yayphotobooks.com/admin/webhooks)).
Add your URL there and copy the signing secret (`whsec_...`).

**Request:**

```http
POST /your/webhook/url HTTP/1.1
Content-Type: application/json
webhook-id: 3f1c9a4e-8b2d-4c6f-9e7a-1d5b8c0f2a94
webhook-timestamp: 1790158500
webhook-signature: v1,K5oZfzN95Z9UVu1EsfQmfVNQhnkZ2pj9o9NDN/H/pI4=
webhook-topic: project.status-updated

{
  "occurredAt": "2026-09-23T08:17:30+00:00",
  "projectId": "550e8400-e29b-41d4-a716-446655440000",
  "status": "TRANSMISSION_FAILED",
  "failedPhotos": [
    { "url": "https://partner.example/photos/123.jpg", "error": "HTTP 404" }
  ]
}
```

`failedPhotos` is only sent with `TRANSMISSION_FAILED`.

**Verify the signature** as defined by [Standard Webhooks](https://www.standardwebhooks.com/), for example with `composer require standard-webhooks/standard-webhooks`:

```php
use StandardWebhooks\Webhook;
use StandardWebhooks\Exception\WebhookVerificationException;

$webhook = new Webhook(getenv('YAY_WEBHOOK_SECRET')); // whsec_...

try {
    $payload = $webhook->verify(
        file_get_contents('php://input'), // the raw body, not re-encoded JSON
        [
            'webhook-id' => $_SERVER['HTTP_WEBHOOK_ID'] ?? '',
            'webhook-timestamp' => $_SERVER['HTTP_WEBHOOK_TIMESTAMP'] ?? '',
            'webhook-signature' => $_SERVER['HTTP_WEBHOOK_SIGNATURE'] ?? '',
        ],
    );
} catch (WebhookVerificationException) {
    http_response_code(401);
    exit;
}

// $payload['projectId'], $payload['status'], $payload['occurredAt']
http_response_code(204);
```

**Rules:**
- Respond with a 2xx status. On any other response we retry with exponential backoff.
- Delivery is at-least-once. Use `webhook-id` to ignore duplicates.
- A project can receive a status more than once: a new preview after changes sends `REVIEW_PENDING` again, and a new order sends `ORDERED`, `IN_PRODUCTION` and `SHIPPED` again.

See the [project lifecycle](docs/getting-started.md) and the full schema in [`openapi-partner-api.yaml`](openapi-partner-api.yaml) (`webhooks` section).

## Environment Configuration

### Required Environment Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `YAY_PARTNER_USERNAME` | Your partner API username | `your_partner_username` |
| `YAY_PARTNER_PASSWORD` | Your partner API password | `your_partner_password` |
| `YAY_PARTNER_USER_AGENT` | Your application identifier | `YourCompany/1.0` |
| `YAY_PARTNER_BASE_URL` | API base URL | `https://sandbox.yayphotobooks.com` |

### API Base URLs

- **Sandbox**: `https://sandbox.yayphotobooks.com`
- **Production**: `https://portal.yayphotobooks.com`
- **Development**: `https://photobooks-portal.local.dev`

The `.envrc` file automatically sets `YAY_PARTNER_BASE_URL` based on your `YAY_PARTNER_ENVIRONMENT` setting.

## Error Handling

The SDK follows RFC 7807 (Problem Details for HTTP APIs) for consistent error handling:

```php
$result = $client->createProject($project);

if ($result->isError()) {
    $error = $result->getError();
    
    echo "Error Type: " . $error->type . "\n";
    echo "Title: " . $error->title . "\n"; 
    echo "Detail: " . $error->detail . "\n";
    echo "Status: " . $error->status . "\n";
    
    // Additional error-specific data
    if (!empty($error->additional)) {
        print_r($error->additional);
    }
    
    // Access original HTTP response for debugging
    $httpResponse = $result->getResponse();
    echo "HTTP Status: " . $httpResponse->getStatusCode() . "\n";
    echo "Response Body: " . $httpResponse->getContent() . "\n";
}
```

## Common Error Codes

| HTTP Status | Error Type | Description |
|-------------|------------|-------------|
| 400 | `validation_failed` | Invalid request data (missing fields, wrong format) |
| 401 | `authentication_failed` | Invalid credentials |
| 404 | `not_found` | Invalid endpoint |
| 500 | `server_error` | Internal YAY error |

## API Reference

### CreateProjectRequest

```php
new CreateProjectRequest(
    title: string,           // Project title
    customer: Customer,   // Customer information
    upload: Upload,       // Upload metadata
    locale: string          // Locale (e.g., "de_DE", "en_US")
)
```

### Customer

```php
new Customer(
    firstname: string,       // Customer first name
    lastname: string,        // Customer last name
    email: string,           // Customer email address
    address: ?Address = null, // Optional: Omit for B2B preview-before-checkout flows; customer provides it at checkout
    phone: ?string = null    // Optional: Mobile phone in E.164 format (e.g. +4917612345678)
)
```

### Address

```php
new Address(
    line1: string,          // Address line 1
    line2: string,          // Address line 2 (can be empty)
    city: string,           // City
    postalCode: string,     // Postal code
    country: string         // ISO 3166-1 alpha-2 country code (e.g., "DE")
)
```

### Upload

```php
new Upload(
    numberOfImages: int,      // Total number of images
    coverUrl: string,         // URL of the cover image
    photoUrls: ?array        // Optional array of photo URLs
)
```

## Development

### Setup Development Environment

```bash
git clone https://github.com/yay-photobooks/partner-sdk.git
cd partner-sdk
composer install
cp .envrc.example .envrc
# Edit .envrc with your credentials
direnv allow
```

### Running Tests

```bash
composer test
```

### Code Quality

```bash
# PHPStan analysis
composer phpstan

# Code style fixes
composer cs-fix

# Run all checks
composer check
```

## Support

- 📖 **API Specification**: [`openapi-partner-api.yaml`](openapi-partner-api.yaml)
- 🐛 **Issues**: [GitHub Issues](https://github.com/yay-photobooks/partner-sdk/issues)
- 💬 **Support**: support@yaymemories.com

## License

This SDK is open-source software licensed under the [MIT License](LICENSE).

---

Made with ❤️ by [YAY Photobooks](https://yayphotobooks.com)
