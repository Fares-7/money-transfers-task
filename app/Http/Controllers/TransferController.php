<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferMoneyRequest;
use App\Http\Resources\TransferResource;
use App\Services\TransferService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Exception;

class TransferController extends Controller
{
    use ApiResponse;

    public function __construct(private TransferService $transferService)
    {}

    public function store(TransferMoneyRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Convert decimal to cents for accurate math
        $amountInCents = intval(round($data['amount'] * 100));

        try {
            $transfer = $this->transferService->processTransfer(
                $data['source_account_id'],
                $data['destination_account_id'],
                $amountInCents
            );

            return $this->successResponse(new TransferResource($transfer), 200);

        } catch (\App\Exceptions\InsufficientBalanceException $e) {
            return $this->failResponse(['message' => $e->getMessage()], 422);
        } catch (\App\Exceptions\SameAccountTransferException $e) {
            return $this->failResponse(['message' => $e->getMessage()], 422);
        } catch (Exception $e) {
            return $this->errorResponse("Transfer failed due to a system error.", 500);
        }
    }
}
