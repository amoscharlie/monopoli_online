<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_cards', function (Blueprint $table) {
            $table->id();
            $table->string('deck');
            $table->string('key')->unique();
            $table->string('title');
            $table->text('description');
            $table->string('effect_type');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('payload')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['deck', 'is_active']);
        });

        Schema::create('game_card_draws', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->nullable()->constrained()->nullOnDelete();
            $table->string('deck');
            $table->enum('status', ['drawn', 'resolved', 'held', 'returned'])->default('drawn');
            $table->json('snapshot')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['game_id', 'deck', 'status']);
        });

        Schema::create('jail_free_card_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('to_player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('game_card_draw_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('amount')->default(3000);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['game_id', 'to_player_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jail_free_card_transfers');
        Schema::dropIfExists('game_card_draws');
        Schema::dropIfExists('game_cards');
    }
};
