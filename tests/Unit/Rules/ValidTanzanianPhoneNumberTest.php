<?php

namespace Tests\Unit\Rules;

use App\Rules\ValidTanzanianPhoneNumber;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidTanzanianPhoneNumberTest extends TestCase
{
    public function test_recognised_formats_are_accepted(): void
    {
        $valid = [
            '0712345678',
            '+255712345678',
            '255712345678',
            '0712 345 678',
            '00 255712345678',
        ];

        foreach ($valid as $phone) {
            $validator = Validator::make(['phone' => $phone], ['phone' => [new ValidTanzanianPhoneNumber]]);

            $this->assertFalse($validator->fails(), "Expected [{$phone}] to be accepted.");
        }
    }

    public function test_unparseable_values_fail_validation_instead_of_throwing(): void
    {
        $invalid = [
            'abc',
            '+255',
            '0123',
            '25512345678',
            '+2551234567890',
            '...',
        ];

        foreach ($invalid as $phone) {
            $validator = Validator::make(['phone' => $phone], ['phone' => [new ValidTanzanianPhoneNumber]]);

            $this->assertTrue($validator->fails(), "Expected [{$phone}] to be rejected.");
        }
    }
}
