<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function getHistoryForAccount(string $accountId, int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::where('account_id', $accountId)
            ->with('transfer')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
