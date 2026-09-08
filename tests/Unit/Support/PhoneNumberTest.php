<?php

namespace Tests\Unit\Support;

use App\Support\PhoneNumber;
use InvalidArgumentException;
use Tests\TestCase;

class PhoneNumberTest extends TestCase
{
    public function test_local_tanzanian_number_is_normalized(): void
    {
        $this->assertSame('+255712345678', PhoneNumber::normalize('0712345678')->value());
    }

    public function test_international_tanzanian_number_is_preserved(): void
    {
        $this->assertSame('+255712345678', PhoneNumber::normalize('+255712345678')->value());
    }

    public function test_invalid_number_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PhoneNumber::normalize('12345');
    }
}
