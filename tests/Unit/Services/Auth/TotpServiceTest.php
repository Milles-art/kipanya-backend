<?php

namespace Tests\Unit\Services\Auth;

use App\Services\Auth\TotpService;
use Tests\TestCase;

class TotpServiceTest extends TestCase
{
    private TotpService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new TotpService;
    }

    public function test_hotp_matches_rfc_4226_reference_vector(): void
    {
        // The base32 representation of the ASCII secret "12345678901234567890"
        // from RFC 4226 Appendix D / RFC 6238 Appendix B.
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

        $this->assertSame('755224', $this->service->codeForCounter($secret, 0));
        $this->assertSame('287082', $this->service->codeForCounter($secret, 1));
        $this->assertSame('359152', $this->service->codeForCounter($secret, 2));
        $this->assertSame('969429', $this->service->codeForCounter($secret, 3));
        $this->assertSame('338314', $this->service->codeForCounter($secret, 4));
        $this->assertSame('254676', $this->service->codeForCounter($secret, 5));
        $this->assertSame('399871', $this->service->codeForCounter($secret, 8));
        $this->assertSame('520489', $this->service->codeForCounter($secret, 9));
    }

    public function test_generated_secret_is_base32_and_accepted_by_verifier(): void
    {
        $secret = $this->service->generateSecret();

        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);
        $this->assertGreaterThanOrEqual(32, strlen($secret));

        $code = $this->service->code($secret);

        $this->assertTrue($this->service->verify($secret, $code));
    }

    public function test_verify_allows_one_step_of_clock_skew(): void
    {
        $secret = $this->service->generateSecret();

        $this->assertTrue($this->service->verify($secret, $this->service->code($secret, -1)));
        $this->assertTrue($this->service->verify($secret, $this->service->code($secret, 1)));
        $this->assertFalse($this->service->verify($secret, $this->service->code($secret, 2)));
    }

    public function test_verify_rejects_malformed_codes(): void
    {
        $secret = $this->service->generateSecret();

        $this->assertFalse($this->service->verify($secret, '12345'));
        $this->assertFalse($this->service->verify($secret, '1234567'));
        $this->assertFalse($this->service->verify($secret, 'abcdef'));
        $this->assertFalse($this->service->verify($secret, '000000'));
        $this->assertFalse($this->service->verify('SHORT', '000000'));
    }

    public function test_provisioning_uri_contains_secret_and_issuer(): void
    {
        $uri = $this->service->provisioningUri('JBSWY3DPEHPK3PXP', 'KP Wear', 'admin');

        $this->assertStringStartsWith('otpauth://totp/', $uri);
        $this->assertStringContainsString('JBSWY3DPEHPK3PXP', $uri);
        $this->assertStringContainsString('issuer=KP+Wear', $uri);
        $this->assertStringContainsString('algorithm=SHA1', $uri);
        $this->assertStringContainsString('digits=6', $uri);
        $this->assertStringContainsString('period=30', $uri);
    }

    public function test_verify_and_consume_rejects_reuse_of_the_same_code(): void
    {
        $secret = $this->service->generateSecret();
        $code = $this->service->code($secret);

        $this->assertTrue($this->service->verifyAndConsume($secret, $code));
        $this->assertFalse($this->service->verifyAndConsume($secret, $code));
        $this->assertTrue($this->service->wasConsumed($secret, $code));

        // verify() itself is a pure check and does not consume.
        $this->assertTrue($this->service->verify($secret, $code));
    }

    public function test_verify_and_consume_blocks_previous_window_replay(): void
    {
        $secret = $this->service->generateSecret();
        $current = $this->service->code($secret, 0);
        $previous = $this->service->code($secret, -1);

        $this->assertTrue($this->service->verifyAndConsume($secret, $current));
        $this->assertFalse($this->service->verifyAndConsume($secret, $previous));
        $this->assertFalse($this->service->wasConsumed($secret, '000000'));
    }

    public function test_verify_and_consume_allows_a_fresh_window_after_consumption(): void
    {
        $secret = $this->service->generateSecret();
        $current = $this->service->code($secret, 0);

        $this->assertTrue($this->service->verifyAndConsume($secret, $current));

        $future = $this->service->code($secret, 1);
        $this->assertTrue($this->service->verifyAndConsume($secret, $future));
    }
}
