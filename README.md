# YAY Partner SDK

[![Latest Version](https://img.shields.io/packagist/v/yay-photobooks/partner-sdk.svg)](https://packagist.org/packages/yay-photobooks/partner-sdk)
[![License](https://img.shields.io/packagist/l/yay-photobooks/partner-sdk.svg)](https://packagist.org/packages/yay-photobooks/partner-sdk)
[![PHP Version](https://img.shields.io/packagist/php-v/yay-photobooks/partner-sdk.svg)](https://packagist.org/packages/yay-photobooks/partner-sdk)

A simple, type-safe PHP SDK for integrating with the YAY Photobook Partner API. Enable YAY to create beautiful photobooks for your customers.

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

## Environment Configuration

### Required Environment Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `YAY_PARTNER_USERNAME` | Your partner API username | `your_partner_username` |
| `YAY_PARTNER_PASSWORD` | Your partner API password | `your_partner_password` |
| `YAY_PARTNER_USER_AGENT` | Your application identifier | `YourCompany/1.0` |
| `YAY_PARTNER_ENVIRONMENT` | API environment | `sandbox` or `production` |

### API Endpoints

- **Sandbox**: `https://sandbox.yaymemories.com/papi/`
- **Production**: `https://portal.yaymemories.com/papi/`

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
    email: string,          // Customer email address
    address: Address     // Customer address
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

- 📖 **Documentation**: [YAY Partner API Docs](https://docs.yaymemories.com/partner-api)
- 🐛 **Issues**: [GitHub Issues](https://github.com/yay-photobooks/partner-sdk/issues)
- 💬 **Support**: support@yaymemories.com

## Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

## License

This SDK is open-source software licensed under the [MIT License](LICENSE).

---

Made with ❤️ by [YAY Photobooks](https://yaymemories.com)