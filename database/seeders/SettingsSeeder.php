<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'starting_balance' => 15000,
            'go_bonus' => 20000,
            'tax_amount' => 20000,
            'fine_amount' => 10000,
            'starting_cash' => 0,
        ] as $key => $value) {
            Setting::putValue($key, $value);
        }
    }
}
