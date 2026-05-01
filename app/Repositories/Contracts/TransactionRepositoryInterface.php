<?php

namespace App\Repositories\Contracts;

use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;

interface TransactionRepositoryInterface
{
    public function create(array $data): Transaction;
    public function getHistoryForAccount(string $accountId, int $perPage = 15): LengthAwarePaginator;
}
