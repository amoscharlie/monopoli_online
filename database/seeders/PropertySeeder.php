<?php

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $properties = [
            ['Indonesia', 'A', 'Komplek A', 'land', 6000, 5000, 25000, 200, 1000, 3000, 9000, 16000, 25000, 3000, '#ef4444'],
            ['Malaysia', 'A', 'Komplek A', 'land', 6000, 5000, 25000, 400, 2000, 6000, 18000, 32000, 45000, 3000, '#ef4444'],
            ['Singapore', 'B', 'Komplek B', 'land', 10000, 5000, 25000, 800, 4000, 10000, 30000, 45000, 60000, 6000, '#f97316'],
            ['Hongkong', 'B', 'Komplek B', 'land', 10000, 5000, 25000, 600, 3000, 9000, 27000, 40000, 55000, 5000, '#f97316'],
            ['Taiwan', 'B', 'Komplek B', 'land', 12000, 5000, 25000, 600, 3000, 9000, 27000, 40000, 55000, 3000, '#f97316'],
            ['Philipina', 'C', 'Komplek C', 'land', 14000, 10000, 50000, 1200, 6000, 18000, 50000, 70000, 90000, 8000, '#facc15'],
            ['Thailand', 'C', 'Komplek C', 'land', 14000, 10000, 50000, 1000, 5000, 15000, 45000, 62500, 75000, 7000, '#facc15'],
            ['Vietnam', 'C', 'Komplek C', 'land', 16000, 10000, 50000, 1000, 5000, 15000, 45000, 62500, 75000, 7000, '#facc15'],
            ['Jepang', 'D', 'Komplek D', 'land', 18000, 10000, 50000, 1400, 7000, 20000, 55000, 75000, 95000, 9000, '#22c55e'],
            ['Korea', 'D', 'Komplek D', 'land', 18000, 10000, 50000, 1600, 8000, 22000, 60000, 80000, 100000, 10000, '#22c55e'],
            ['India', 'D', 'Komplek D', 'land', 20000, 10000, 50000, 1400, 7000, 20000, 55000, 75000, 95000, 10000, '#22c55e'],
            ['RRC China', 'E', 'Komplek E', 'land', 22000, 15000, 75000, 1800, 9000, 25000, 70000, 87500, 105000, 11000, '#fb7185'],
            ['Uni Soviet', 'E', 'Komplek E', 'land', 22000, 15000, 75000, 2000, 10000, 30000, 75000, 92500, 110000, 12000, '#fb7185'],
            ['Italia', 'E', 'Komplek E', 'land', 24000, 15000, 75000, 1800, 9000, 25000, 70000, 87500, 105000, 11000, '#fb7185'],
            ['Inggris', 'F', 'Komplek F', 'land', 26000, 15000, 75000, 2400, 12000, 36000, 85000, 102000, 120000, 14000, '#b91c1c'],
            ['Perancis', 'F', 'Komplek F', 'land', 26000, 15000, 75000, 2200, 11000, 33000, 80000, 97500, 115000, 13000, '#b91c1c'],
            ['Belanda', 'F', 'Komplek F', 'land', 28000, 15000, 75000, 2200, 11000, 33000, 80000, 97500, 115000, 13000, '#b91c1c'],
            ['Kanada', 'G', 'Komplek G', 'land', 30000, 20000, 100000, 2600, 13000, 39000, 90000, 110000, 127500, 15000, '#0ea5e9'],
            ['Amerika Serikat', 'G', 'Komplek G', 'land', 30000, 20000, 100000, 2800, 15000, 45000, 100000, 120000, 140000, 16000, '#0ea5e9'],
            ['Brazilia', 'G', 'Komplek G', 'land', 32000, 20000, 100000, 2600, 13000, 39000, 90000, 110000, 127500, 15000, '#0ea5e9'],
            ['Australia', 'H', 'Komplek H', 'land', 35000, 20000, 100000, 3500, 17500, 50000, 110000, 130000, 150000, 17000, '#dc2626'],
            ['Mesir', 'H', 'Komplek H', 'land', 40000, 20000, 100000, 5000, 20000, 60000, 140000, 170000, 200000, 20000, '#dc2626'],
            ['Pelabuhan Sidney', 'TRANSPORT', 'Transportasi', 'transport', 20000, 0, 0, 2500, 5000, 10000, 20000, 0, 0, 10000, '#64748b'],
            ['Changi Airport', 'TRANSPORT', 'Transportasi', 'transport', 20000, 0, 0, 2500, 5000, 10000, 20000, 0, 0, 10000, '#64748b'],
            ['Stasiun London', 'TRANSPORT', 'Transportasi', 'transport', 20000, 0, 0, 2500, 5000, 10000, 20000, 0, 0, 10000, '#64748b'],
            ['Terminal Bus Tokyo', 'TRANSPORT', 'Transportasi', 'transport', 20000, 0, 0, 2500, 5000, 10000, 20000, 0, 0, 10000, '#64748b'],
            ['Perusahaan Listrik', 'UTILITY', 'Perusahaan', 'utility', 7500, 0, 0, 0, 0, 0, 0, 0, 0, 0, '#facc15'],
            ['Perusahaan Air', 'UTILITY', 'Perusahaan', 'utility', 7500, 0, 0, 0, 0, 0, 0, 0, 0, 0, '#38bdf8'],
        ];

        $names = collect($properties)->pluck(0)->all();

        Property::query()
            ->whereNotIn('name', $names)
            ->delete();

        foreach ($properties as $index => $row) {
            Property::query()->updateOrCreate(
                ['name' => $row[0]],
                [
                    'group_code' => $row[1],
                    'group_name' => $row[2],
                    'property_kind' => $row[3],
                    'price' => $row[4],
                    'house_price' => $row[5],
                    'hotel_price' => $row[6],
                    'rent' => $row[7],
                    'rent_1_house' => $row[8],
                    'rent_2_houses' => $row[9],
                    'rent_3_houses' => $row[10],
                    'rent_4_houses' => $row[11],
                    'rent_hotel' => $row[12],
                    'mortgage_value' => $row[13],
                    'color' => $row[14],
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
