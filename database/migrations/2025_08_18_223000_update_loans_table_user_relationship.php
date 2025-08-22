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
        Schema::table('loans', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['member_id']);
            $table->dropForeign(['account_id']);

            // Drop the columns
            $table->dropColumn(['member_id', 'account_id']);

            // Add user_id column
            $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            // Drop user_id foreign key and column
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id']);

            // Add back member_id and account_id columns
            $table->foreignId('member_id')->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->after('member_id')->constrained()->onDelete('cascade');
        });
    }
};
