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
        Schema::create('user_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('share_id')->constrained()->onDelete('cascade');
            $table->integer('quantity'); // Number of shares owned
            $table->decimal('purchase_price', 15, 2); // Price paid per share
            $table->decimal('total_paid', 15, 2); // Total amount paid
            $table->timestamp('purchase_date'); // When purchased
            $table->boolean('is_active')->default(true); // Active ownership
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id']);
            $table->index(['share_id']);
            $table->index(['is_active']);
            $table->index(['purchase_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_shares');
    }
};
