<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->foreignId('current_turn_player_id')->nullable()->after('first_player_id')->constrained('players')->nullOnDelete();
            $table->unsignedInteger('turn_number')->default(1)->after('current_turn_player_id');
            $table->json('turn_order')->nullable()->after('turn_number');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->boolean('is_in_jail')->default(false)->after('is_bankrupt');
            $table->unsignedTinyInteger('double_streak')->default(0)->after('is_in_jail');
            $table->unsignedTinyInteger('jail_turn_count')->default(0)->after('double_streak');
            $table->boolean('pending_jail_release')->default(false)->after('jail_turn_count');
            $table->unsignedTinyInteger('jail_free_cards')->default(0)->after('pending_jail_release');
        });

        Schema::create('dice_rolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('dice_one');
            $table->unsignedTinyInteger('dice_two');
            $table->unsignedTinyInteger('total');
            $table->boolean('is_double')->default(false);
            $table->unsignedInteger('turn_number')->default(1);
            $table->string('result')->default('normal');
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dice_rolls');

        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn([
                'is_in_jail',
                'double_streak',
                'jail_turn_count',
                'pending_jail_release',
                'jail_free_cards',
            ]);
        });

        Schema::table('games', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_turn_player_id');
            $table->dropColumn(['turn_number', 'turn_order']);
        });
    }
};
