<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Services\AccountService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class AccountController extends Controller
{
    use ApiResponse;

    public function __construct(private AccountService $accountService)
    {}

    public function store(CreateAccountRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Convert to cents
        $data['balance'] = intval(round($data['balance'] * 100));

        $account = $this->accountService->createAccount($data);
        
        return $this->successResponse(new AccountResource($account), 201);
    }
}
