<?php

namespace Tests\Unit\Services\Auth;

use App\Enums\Auth\OtpPurpose;
use App\Integrations\Sms\SmsGateway;
use App\Models\Auth\OtpCode;
use App\Services\Auth\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class OtpServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_otp_is_hashed_and_sent(): void
    {
        $sms = Mockery::mock(SmsGateway::class);
        $sms->shouldReceive('send')
            ->once()
            ->withArgs(fn (string $phone, string $message) =>
                $phone === '+255712345678' && str_contains($message, 'Kipanya')
            );

        $service = new OtpService($sms);
        $otp = $service->send('0712345678', OtpPurpose::Registration);

        $this->assertNotEmpty($otp->code_hash);
        $this->assertNotSame('123456', $otp->code_hash);
        $this->assertNull($otp->verified_at);
    }

    public function test_invalid_code_increments_attempts(): void
    {
        $sms = Mockery::mock(SmsGateway::class);
        $sms->shouldReceive('send')->once();

        $service = new OtpService($sms);
        $otp = $service->send('0712345678', OtpPurpose::Registration);

        $this->assertThrows(
            fn () => $service->verify('0712345678', OtpPurpose::Registration, '000000'),
            ValidationException::class
        );

        $this->assertSame(1, $otp->fresh()->attempts);
    }

    public function test_verified_otp_cannot_be_reused(): void
    {
        $sms = Mockery::mock(SmsGateway::class);
        $sms->shouldReceive('send')->once();

        $service = new OtpService($sms);
        $otp = $service->send('0712345678', OtpPurpose::Registration);

        // The exact code is deliberately not exposed by the production service.
        // Verification behavior is covered through a controlled hash in this test.
        $otp->forceFill(['code_hash' => Hash::make('123456')])->save();

        $service->verify('0712345678', OtpPurpose::Registration, '123456');

        $this->assertThrows(
            fn () => $service->verify('0712345678', OtpPurpose::Registration, '123456'),
            ValidationException::class
        );
    }
}
