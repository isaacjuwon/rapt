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
        Schema::create('shares', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Share class name (e.g., "Class A Shares", "Founder Shares")
            $table->text('description')->nullable(); // Description of share benefits
            $table->bigInteger('total_shares'); // Total shares in this class
            $table->bigInteger('available_shares'); // Shares available for purchase
            $table->decimal('price_per_share', 15, 2); // Price per share
            $table->integer('minimum_purchase')->default(1); // Minimum shares to buy
            $table->integer('maximum_purchase')->nullable(); // Maximum shares per user
            $table->decimal('dividend_rate', 5, 4)->nullable(); // Annual dividend rate %
            $table->decimal('revenue_share_percentage', 5, 4)->nullable(); // % of site revenue
            $table->boolean('voting_rights')->default(false); // Can vote on site decisions
            $table->boolean('is_active')->default(true); // Can be purchased
            $table->boolean('is_transferable')->default(true); // Can be transferred between users
            $table->timestamp('launch_date')->nullable(); // When shares become available
            $table->json('metadata')->nullable(); // Additional share data
            $table->timestamps();

            // Indexes for performance
            $table->index(['is_active']);
            $table->index(['available_shares']);
            $table->index(['launch_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shares');
    }
};
