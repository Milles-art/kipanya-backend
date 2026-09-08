<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentTransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'provider_reference' => $this->provider_reference,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            'initiated_at' => optional($this->initiated_at)?->toISOString(),
            'completed_at' => optional($this->completed_at)?->toISOString(),
            'failed_at' => optional($this->failed_at)?->toISOString(),
        ];
    }
}
