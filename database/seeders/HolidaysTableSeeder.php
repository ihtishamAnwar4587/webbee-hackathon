<?php

namespace Database\Seeders;

use App\Models\Holiday;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HolidaysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Public Holiday',
                'date' => now()->addDays(2)->format('Y-m-d'),
            ]
        ];

        Holiday::insert($data);
        $holidays = Holiday::all();
        $services = Service::all();

        foreach ( $services as $service ){
            foreach ( $holidays as $holiday ){
                $service->holidays()->save($holiday);
            }
        }

    }
}
