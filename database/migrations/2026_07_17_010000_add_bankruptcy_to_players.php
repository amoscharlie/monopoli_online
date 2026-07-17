<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->boolean('is_bankrupt')->default(false)->after('cash_balance');
            $table->timestamp('bankrupted_at')->nullable()->after('is_bankrupt');
            $table->json('bankrupt_summary')->nullable()->after('bankrupted_at');
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn(['is_bankrupt', 'bankrupted_at', 'bankrupt_summary']);
        });
    }
};
