<?php

namespace App\Repositories;

use App\Models\Transfer;
use App\Repositories\Contracts\TransferRepositoryInterface;

class TransferRepository implements TransferRepositoryInterface
{
    public function create(array $data): Transfer
    {
        return Transfer::create($data);
    }

    public function updateStatus(Transfer $transfer, string $status, ?string $failureReason = null): bool
    {
        $transfer->status = $status;
        if ($failureReason !== null) {
            $transfer->failure_reason = $failureReason;
        }
        return $transfer->save();
    }
}
