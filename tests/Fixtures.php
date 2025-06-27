<?php declare(strict_types=1);

namespace YAY\PartnerSDK\Tests;

use YAY\PartnerSDK\Dto\V1;

class Fixtures
{
    public static function createValidCustomer(): V1\Customer
    {
        return new V1\Customer(
            firstname: 'Sarah',
            lastname: 'Mueller',
            email: 'sarah.mueller@gmail.com',
            address: new V1\Address(
                line1: 'Musterstraße 123',
                line2: 'Apartment 4B',
                city: 'Berlin',
                postalCode: '10115',
                country: 'DE'
            )
        );
    }
}
