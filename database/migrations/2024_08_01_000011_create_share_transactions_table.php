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
        Schema::create('share_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('share_id')->constrained()->onDelete('cascade');
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade'); // Which wallet was used
            $table->string('transaction_id')->unique(); // Unique transaction identifier
            $table->enum('type', ['buy', 'sell', 'transfer']);
            $table->integer('quantity'); // Number of shares
            $table->decimal('price_per_share', 15, 2); // Price at time of transaction
            $table->decimal('total_amount', 15, 2); // Total transaction amount
            $table->decimal('fees', 15, 2)->default(0); // Transaction fees
            $table->decimal('net_amount', 15, 2); // Amount after fees
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable(); // Transaction notes
            $table->json('metadata')->nullable(); // Additional transaction data
            $table->foreignId('from_user_id')->nullable()->constrained('users'); // For transfers
            $table->foreignId('to_user_id')->nullable()->constrained('users'); // For transfers
            $table->timestamp('executed_at')->nullable(); // When transaction was executed
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'created_at']);
            $table->index(['share_id', 'created_at']);
            $table->index(['wallet_id']);
            $table->index(['type']);
            $table->index(['status']);
            $table->index(['transaction_id']);
            $table->index(['executed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_transactions');
    }
};
