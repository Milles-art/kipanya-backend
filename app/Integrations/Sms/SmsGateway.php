<?php

namespace App\Integrations\Sms;

interface SmsGateway
{
    public function send(string $phone, string $message): void;
}
