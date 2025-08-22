<?php

declare(strict_types=1);

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
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('suspended_until')->nullable()->after('email_verified_at');
            $table->text('suspension_reason')->nullable()->after('suspended_until');
            
            $table->index(['suspended_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['suspended_until']);
            $table->dropColumn(['suspended_until', 'suspension_reason']);
        });
    }
};
