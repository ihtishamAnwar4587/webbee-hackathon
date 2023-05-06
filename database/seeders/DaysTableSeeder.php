<?php

namespace Database\Seeders;

use App\Models\Day;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DaysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Monday',
                'start_time' => '08:00:00',
                'end_time' => '20:00:00',
            ],
            [
                'name' => 'Tuesday',
                'start_time' => '08:00:00',
                'end_time' => '20:00:00',
            ],
            [
                'name' => 'Wednesday',
                'start_time' => '08:00:00',
                'end_time' => '20:00:00',
            ],
            [
                'name' => 'Thursday',
                'start_time' => '08:00:00',
                'end_time' => '20:00:00',
            ],
            [
                'name' => 'Friday',
                'start_time' => '08:00:00',
                'end_time' => '20:00:00',
            ],
            [
                'name' => 'Saturday',
                'start_time' => '10:00:00',
                'end_time' => '22:00:00',
            ],
            [
                'name' => 'Sunday',
                'start_time' => '00:00:00',
                'end_time' => '00:00:00',
            ],
        ];

        Day::insert($data);
    }
}
