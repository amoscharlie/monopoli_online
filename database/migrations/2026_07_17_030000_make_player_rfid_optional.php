<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE players MODIFY rfid_uid VARCHAR(255) NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("UPDATE players SET rfid_uid = CONCAT('MANUAL-', id) WHERE rfid_uid IS NULL OR rfid_uid = ''");
        DB::statement('ALTER TABLE players MODIFY rfid_uid VARCHAR(255) NOT NULL');
    }
};
