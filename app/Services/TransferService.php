<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\SameAccountTransferException;
use App\Models\Transfer;
use App\Repositories\Contracts\AccountRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Repositories\Contracts\TransferRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class TransferService
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private TransferRepositoryInterface $transferRepository,
        private TransactionRepositoryInterface $transactionRepository
    ) {}

    public function processTransfer(string $sourceAccountId, string $destinationAccountId, int $amount): Transfer
    {
        if ($sourceAccountId === $destinationAccountId) {
            throw new SameAccountTransferException();
        }

        // Create the transfer record in 'pending' status immediately for tracking
        $transfer = $this->transferRepository->create([
            'source_account_id'      => $sourceAccountId,
            'destination_account_id' => $destinationAccountId,
            'amount'                 => $amount,
            'status'                 => 'pending',
        ]);

        try {
            DB::transaction(function () use ($sourceAccountId, $destinationAccountId, $amount, $transfer) {
                // Lock the accounts deterministically to prevent deadlocks
                $accounts = $this->accountRepository->getAccountsByIdsForUpdate([$sourceAccountId, $destinationAccountId]);

                $sourceAccount = $accounts->get($sourceAccountId);
                $destinationAccount = $accounts->get($destinationAccountId);

                if (!$sourceAccount || !$destinationAccount) {
                    throw new Exception("One or both accounts not found.");
                }

                if ($sourceAccount->balance < $amount) {
                    throw new InsufficientBalanceException();
                }

                $sourceBalanceBefore = $sourceAccount->balance;
                $destinationBalanceBefore = $destinationAccount->balance;

                // Update balances
                $this->accountRepository->updateBalance($sourceAccount, $sourceAccount->balance - $amount);
                $this->accountRepository->updateBalance($destinationAccount, $destinationAccount->balance + $amount);

                // Create double-entry ledger transactions
                $this->transactionRepository->create([
                    'transfer_id'    => $transfer->id,
                    'account_id'     => $sourceAccountId,
                    'type'           => 'debit',
                    'amount'         => $amount,
                    'balance_before' => $sourceBalanceBefore,
                    'balance_after'  => $sourceAccount->balance,
                ]);

                $this->transactionRepository->create([
                    'transfer_id'    => $transfer->id,
                    'account_id'     => $destinationAccountId,
                    'type'           => 'credit',
                    'amount'         => $amount,
                    'balance_before' => $destinationBalanceBefore,
                    'balance_after'  => $destinationAccount->balance,
                ]);

                $this->transferRepository->updateStatus($transfer, 'success');
            });
        } catch (Exception $e) {
            $this->transferRepository->updateStatus($transfer, 'failed', $e->getMessage());
            throw $e;
        }

        return $transfer;
    }
}
