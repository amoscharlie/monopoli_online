<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->timestamp('turn_started_at')->nullable()->after('turn_number');
        });

        DB::table('games')
            ->whereNotNull('current_turn_player_id')
            ->where('status', 'active')
            ->update(['turn_started_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('turn_started_at');
        });
    }
};
