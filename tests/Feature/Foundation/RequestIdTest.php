<?php

namespace Tests\Feature\Foundation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regression coverage: a client-supplied X-Request-ID is only trusted when it
 * is a well-formed UUID; anything else is replaced with a generated UUID so the
 * value can never be reflected back verbatim.
 */
class RequestIdTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/wear/products';

    public function test_missing_request_id_is_generated_as_a_uuid(): void
    {
        $response = $this->getJson(self::ENDPOINT);

        $response->assertOk();
        $this->assertTrue(Str::isUuid((string) $response->headers->get('X-Request-ID')));
    }

    public function test_valid_request_id_is_echoed_back(): void
    {
        $id = (string) Str::uuid();

        $response = $this->withHeaders(['X-Request-ID' => $id])->getJson(self::ENDPOINT);

        $response->assertOk();
        $this->assertSame($id, $response->headers->get('X-Request-ID'));
    }

    public function test_invalid_request_id_is_replaced_with_a_uuid(): void
    {
        $invalid = ['not-a-uuid', '../../etc/passwd', '<script>alert(1)</script>', '12345'];

        foreach ($invalid as $value) {
            $response = $this->withHeaders(['X-Request-ID' => $value])->getJson(self::ENDPOINT);

            $response->assertOk();
            $header = (string) $response->headers->get('X-Request-ID');

            $this->assertNotSame($value, $header);
            $this->assertTrue(Str::isUuid($header), "Expected [{$value}] to be replaced with a UUID.");
        }
    }
}
