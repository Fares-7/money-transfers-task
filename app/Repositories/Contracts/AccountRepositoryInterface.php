<?php

namespace App\Repositories\Contracts;

use App\Models\Account;
use Illuminate\Database\Eloquent\Collection;

interface AccountRepositoryInterface
{
    public function create(array $data): Account;
    public function findById(string $id): ?Account;
    public function getAccountsByIdsForUpdate(array $ids): Collection;
    public function updateBalance(Account $account, int $newBalance): bool;
}
