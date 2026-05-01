<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('source_account_id')->constrained('accounts');
            $table->foreignUuid('destination_account_id')->constrained('accounts');
            $table->unsignedBigInteger('amount');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->string('failure_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
