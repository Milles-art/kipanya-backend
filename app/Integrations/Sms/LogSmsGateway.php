<?php

namespace App\Integrations\Sms;

use Illuminate\Support\Facades\Log;
use RuntimeException;

final class LogSmsGateway implements SmsGateway
{
    public function send(string $phone, string $message): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException(
                'LogSmsGateway cannot be used in production. Bind a real SmsGateway implementation.'
            );
        }

        $context = ['phone' => $phone];

        if (config('auth.log_otp_codes', false)) {
            $context['message'] = $message;
        }

        Log::info('SMS dispatched', $context);
    }
}
