<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class TransactionController extends Controller
{
    use ApiResponse;

    public function __construct(private TransactionRepositoryInterface $transactionRepository)
    {}

    public function index(string $accountId): JsonResponse
    {
        $transactions = $this->transactionRepository->getHistoryForAccount($accountId);
        
        return $this->successResponse([
            'transactions' => TransactionResource::collection($transactions),
            'pagination'   => [
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'total'        => $transactions->total(),
            ]
        ], 200);
    }
}
