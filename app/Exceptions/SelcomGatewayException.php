<?php

namespace App\Exceptions;

use RuntimeException;

class SelcomGatewayException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly bool $isTimeout,
        public readonly ?int $httpStatus = null,
        public readonly ?string $providerCode = null,
        public readonly ?string $providerMessage = null,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function timeout(string $message = 'Selcom Checkout request timed out'): self
    {
        return new self($message, isTimeout: true);
    }

    public static function connectionFailed(string $message = 'Could not connect to Selcom Checkout'): self
    {
        return new self($message, isTimeout: true);
    }

    public static function providerError(
        int $httpStatus,
        ?string $providerCode = null,
        ?string $providerMessage = null,
    ): self {
        $detail = '';
        if ($providerCode || $providerMessage) {
            $detail = ' ('.trim(implode(' ', array_filter([$providerCode, $providerMessage]))).')';
        }

        return new self(
            'Selcom Checkout request failed with HTTP '.$httpStatus.$detail,
            isTimeout: false,
            httpStatus: $httpStatus,
            providerCode: $providerCode,
            providerMessage: $providerMessage,
        );
    }
}
