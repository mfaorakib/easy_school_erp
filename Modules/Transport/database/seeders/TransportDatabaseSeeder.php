<?php

namespace Modules\Transport\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Transport\Models\TransportRoute;
use Modules\Transport\Models\Vehicle;

class TransportDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $routeA = TransportRoute::firstOrCreate(['name' => 'Route A'], ['fare' => 800, 'start_point' => 'Downtown', 'end_point' => 'School']);
        TransportRoute::firstOrCreate(['name' => 'Route B'], ['fare' => 1200, 'start_point' => 'Uptown', 'end_point' => 'School']);

        $bus1 = Vehicle::firstOrCreate(['vehicle_no' => 'DHK-1234'], ['model' => 'Bus', 'driver_name' => 'Jamal', 'driver_phone' => '01710000001', 'capacity' => 40]);
        Vehicle::firstOrCreate(['vehicle_no' => 'DHK-5678'], ['model' => 'Minibus', 'driver_name' => 'Selim', 'driver_phone' => '01710000002', 'capacity' => 20]);

        $routeA->vehicles()->syncWithoutDetaching([$bus1->id]);
    }
}
