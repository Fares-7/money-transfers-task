<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'source_account_id'      => $this->source_account_id,
            'destination_account_id' => $this->destination_account_id,
            'amount'                 => number_format($this->amount / 100, 2, '.', ''),
            'status'                 => $this->status,
            'failure_reason'         => $this->failure_reason,
            'created_at'             => $this->created_at?->toIso8601String(),
        ];
    }
}
