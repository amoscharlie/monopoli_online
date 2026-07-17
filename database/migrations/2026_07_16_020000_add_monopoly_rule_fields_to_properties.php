<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('group_code')->nullable()->after('name');
            $table->string('group_name')->nullable()->after('group_code');
            $table->string('property_kind')->default('land')->after('group_name');
            $table->unsignedInteger('mortgage_value')->default(0)->after('rent_hotel');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['group_code', 'group_name', 'property_kind', 'mortgage_value']);
        });
    }
};
