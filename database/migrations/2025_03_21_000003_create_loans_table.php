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
        Schema::dropIfExists('loans');
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->string('loan_number')->unique();
            $table->enum('loan_type', ['personal', 'business', 'education', 'agriculture', 'emergency']);
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->decimal('total_payable', 15, 2);
            $table->decimal('total_paid', 15, 2)->default(0);
            $table->decimal('remaining_balance', 15, 2);
            $table->integer('term_months');
            $table->integer('total_installments');
            $table->integer('paid_installments')->default(0);
            $table->date('disbursement_date');
            $table->date('first_payment_date');
            $table->date('last_payment_date')->nullable();
            $table->date('expected_end_date');
            $table->enum('payment_frequency', ['weekly', 'biweekly', 'monthly']);
            $table->enum('status', ['pending', 'approved', 'disbursed', 'active', 'completed', 'defaulted', 'rejected'])->default('pending');
            $table->text('purpose')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
