<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_transfer_money_between_accounts()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $account1 = Account::factory()->create([
            'user_id' => $user1->id,
            'balance' => 100000, // $1000.00
        ]);

        $account2 = Account::factory()->create([
            'user_id' => $user2->id,
            'balance' => 50000, // $500.00
        ]);

        $response = $this->postJson('/api/v1/transfers', [
            'source_account_id' => $account1->id,
            'destination_account_id' => $account2->id,
            'amount' => 150.50,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.status', 'success');
        $response->assertJsonPath('data.amount', '150.50');

        $this->assertDatabaseHas('accounts', [
            'id' => $account1->id,
            'balance' => 84950, // $1000.00 - $150.50 = $849.50 (84950 cents)
        ]);

        $this->assertDatabaseHas('accounts', [
            'id' => $account2->id,
            'balance' => 65050, // $500.00 + $150.50 = $650.50 (65050 cents)
        ]);

        $this->assertDatabaseCount('transactions', 2);
    }

    public function test_cannot_transfer_with_insufficient_balance()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $account1 = Account::factory()->create([
            'user_id' => $user1->id,
            'balance' => 1000, // $10.00
        ]);

        $account2 = Account::factory()->create([
            'user_id' => $user2->id,
            'balance' => 0,
        ]);

        $response = $this->postJson('/api/v1/transfers', [
            'source_account_id' => $account1->id,
            'destination_account_id' => $account2->id,
            'amount' => 50.00, // $50.00
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('data.message', 'Insufficient balance for this transfer.');

        // Verify balance hasn't changed
        $this->assertDatabaseHas('accounts', [
            'id' => $account1->id,
            'balance' => 1000,
        ]);
        
        // Verify failed transfer record is kept
        $this->assertDatabaseHas('transfers', [
            'status' => 'failed',
            'failure_reason' => 'Insufficient balance for this transfer.',
        ]);
    }
}
