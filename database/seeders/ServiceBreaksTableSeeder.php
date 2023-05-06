<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceBreak;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceBreaksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Lunch Break',
                'start_time' => '12:00:00',
                'end_time' => '13:00:00',
            ],
            [
                'name' => 'Cleaning Break',
                'start_time' => '15:00:00',
                'end_time' => '16:00:00',
            ]
        ];

        ServiceBreak::insert($data);
        $serviceBreaks = ServiceBreak::all();
        $services = Service::all();

        foreach ( $services as $service ){
            foreach ( $serviceBreaks as $break ){
                $service->breaks()->save($break);
            }
        }

    }
}
