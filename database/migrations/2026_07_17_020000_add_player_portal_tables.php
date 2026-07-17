<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->string('token', 80)->unique();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('transaction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->string('type', 60);
            $table->foreignId('target_player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('game_property_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('amount')->nullable();
            $table->string('source', 20)->nullable();
            $table->string('reason', 160)->nullable();
            $table->string('status', 20)->default('pending');
            $table->json('payload')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_requests');
        Schema::dropIfExists('player_access_tokens');
    }
};
