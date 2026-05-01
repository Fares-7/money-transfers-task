<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {}

    public function createUser(array $data): User
    {
        return $this->userRepository->create($data);
    }
}
