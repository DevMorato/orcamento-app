<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('family_id')->constrained()->nullOnDelete();
            $table->foreignId('recurring_transaction_id')->nullable()->after('account_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropForeign(['recurring_transaction_id']);
            $table->dropColumn(['account_id', 'recurring_transaction_id']);
        });
    }
};
