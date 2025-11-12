<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use YAY\PartnerSDK\Client;
use YAY\PartnerSDK\Dto\V1;

try {
    // Make sure environment variables are set (via .envrc or manually)
    $client = new Client(
        \YAY\PartnerSDK\Configuration::fromEnvironment()
    );

    echo "📝 Creating photobook project...\n";
    try {
        $response = $client->createProject(
            new V1\CreateProjectRequest(
                title: "Sarah und Ben's Wedding Album",
                customer: new V1\Customer(
                    firstname: "Sarah",
                    lastname: "Mueller",
                    email: "sarah.mueller@example.com",
                    address: new V1\Address(
                        line1: "Musterstraße 123",
                        line2: "Apartment 4B",
                        city: "Frankfurt",
                        postalCode: "10115",
                        country: "DE"
                    )
                ),
                upload: new V1\Upload(
                    numberOfImages: 800,
                    coverUrl: "https://picsum.photos/1000/800.jpg",
                    photoUrls: [
                        "https://picsum.photos/seed/wedding1/1000/800.jpg",
                        "https://picsum.photos/seed/wedding2/1000/800.jpg",
                        "https://picsum.photos/seed/wedding3/1000/800.jpg",
                        "https://picsum.photos/seed/wedding4/1000/800.jpg",
                    ]
                ),
                locale: "de_DE"
            )
        );

        echo "✅ Project created successfully!\n";
        echo "Project ID: " . $response->result->projectId . "\n";
        echo "Redirect your customer to: " . $response->result->redirectUrl . "\n\n";

        echo "🔍 Debug Info:\n " . $response->getDebug();
    } catch (\YAY\PartnerSDK\Exception\ServerErrorException $e) {
        $problem = $e->problem;
        echo "❌ Error creating project:\n";
        echo json_encode($problem, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        echo "🔍 Debug Info:\n " . $e->getDebug();
    }

} catch (\YAY\PartnerSDK\Exception\RuntimeException $e) {
    echo "💥 Unexpected SDK Error: " . $e . "\n";
}
