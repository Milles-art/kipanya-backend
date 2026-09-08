<?php

namespace App\Integrations\Sms;

use Illuminate\Support\Facades\Log;

final class LogSmsGateway implements SmsGateway
{
    public function send(string $phone, string $message): void
    {
        $context = ['phone' => $phone];

        if (config('auth.log_otp_codes', false)) {
            $context['message'] = $message;
        }

        Log::info('SMS dispatched', $context);
    }
}
