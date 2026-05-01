<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'transfer_id'    => $this->transfer_id,
            'type'           => $this->type,
            'amount'         => number_format($this->amount / 100, 2, '.', ''),
            'balance_before' => number_format($this->balance_before / 100, 2, '.', ''),
            'balance_after'  => number_format($this->balance_after / 100, 2, '.', ''),
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}
