<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->unsignedTinyInteger('board_position')->default(0)->after('sort_order');
            $table->unsignedSmallInteger('lap_count')->default(0)->after('board_position');
            $table->boolean('rules_unlocked')->default(false)->after('lap_count');
            $table->json('pending_space_action')->nullable()->after('rules_unlocked');
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn([
                'board_position',
                'lap_count',
                'rules_unlocked',
                'pending_space_action',
            ]);
        });
    }
};
