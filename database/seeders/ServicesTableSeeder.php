<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Men Haircut',
                'slug' => 'men_haircut',
                'duration_in_mins' => 10,
                'duration_in_mins_after_appointment' => 5,
                'max_client_per_appointment' => 3,
                'slots_for_next_days' => 7,
            ],
            [
                'name' => 'Women Haircut',
                'slug' => 'women_haircut',
                'duration_in_mins' => 60,
                'duration_in_mins_after_appointment' => 10,
                'max_client_per_appointment' => 3,
                'slots_for_next_days' => 7,
            ]
        ];

        Service::insert($data);
    }
}
