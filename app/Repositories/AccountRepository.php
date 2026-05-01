<?php

namespace App\Repositories;

use App\Models\Account;
use App\Repositories\Contracts\AccountRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AccountRepository implements AccountRepositoryInterface
{
    public function create(array $data): Account
    {
        return Account::create($data);
    }

    public function findById(string $id): ?Account
    {
        return Account::find($id);
    }

    public function getAccountsByIdsForUpdate(array $ids): Collection
    {
        // lockForUpdate prevents deadlocks if we consistently order by ID
        return Account::whereIn('id', $ids)
            ->orderBy('id', 'asc')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    public function updateBalance(Account $account, int $newBalance): bool
    {
        $account->balance = $newBalance;
        return $account->save();
    }
}
