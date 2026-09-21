<?php

namespace App\Support;

use App\Integrations\Sms\SmsGateway;
use Illuminate\Support\Facades\DB;

/**
 * Best-effort SMS to the operations phone. Sent only AFTER the surrounding database
 * transaction commits (never holds locks while calling the SMS provider) and never
 * lets a provider failure break payment processing. Messages carry no customer data.
 */
final class OpsAlert
{
    public static function notify(string $message): void
    {
        $phone = trim((string) config('security.alert_phone', ''));

        if ($phone === '') {
            return;
        }

        DB::afterCommit(static function () use ($phone, $message): void {
            try {
                app(SmsGateway::class)->send($phone, mb_substr($message, 0, 300));
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}
