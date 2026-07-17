<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('status', ['active', 'paused', 'finished'])->default('active');
            $table->unsignedInteger('starting_balance')->default(15000);
            $table->unsignedInteger('go_bonus')->default(200);
            $table->unsignedInteger('tax_amount')->default(200);
            $table->unsignedInteger('fine_amount')->default(50);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->foreignId('winner_id')->nullable()->index();
            $table->foreignId('last_scanned_player_id')->nullable()->index();
            $table->json('finish_summary')->nullable();
            $table->timestamps();
        });

        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('price');
            $table->unsignedInteger('house_price')->default(0);
            $table->unsignedInteger('hotel_price')->default(0);
            $table->unsignedInteger('rent')->default(0);
            $table->unsignedInteger('rent_1_house')->default(0);
            $table->unsignedInteger('rent_2_houses')->default(0);
            $table->unsignedInteger('rent_3_houses')->default(0);
            $table->unsignedInteger('rent_4_houses')->default(0);
            $table->unsignedInteger('rent_hotel')->default(0);
            $table->string('color')->default('#10b981');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('rfid_uid')->nullable();
            $table->string('avatar_color')->default('#10b981');
            $table->integer('balance')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['game_id', 'rfid_uid']);
        });

        Schema::create('game_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('players')->nullOnDelete();
            $table->unsignedTinyInteger('house_count')->default(0);
            $table->boolean('has_hotel')->default(false);
            $table->boolean('is_mortgaged')->default(false);
            $table->timestamps();

            $table->unique(['game_id', 'property_id']);
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->integer('amount')->default(0);
            $table->foreignId('from_player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('to_player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('game_property_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->integer('balance_after_from')->nullable();
            $table->integer('balance_after_to')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value');
            $table->timestamps();
        });

        Schema::create('rfid_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->string('uid');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('last_scanned_at')->nullable();
            $table->timestamps();

            $table->unique(['game_id', 'uid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfid_cards');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('game_properties');
        Schema::dropIfExists('players');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('games');
    }
};
