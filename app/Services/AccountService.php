<?php

namespace App\Services;

use App\Models\Account;
use App\Repositories\Contracts\AccountRepositoryInterface;

class AccountService
{
    public function __construct(private AccountRepositoryInterface $accountRepository)
    {}

    public function createAccount(array $data): Account
    {
        return $this->accountRepository->create($data);
    }

    public function getAccount(string $id): ?Account
    {
        return $this->accountRepository->findById($id);
    }
}
