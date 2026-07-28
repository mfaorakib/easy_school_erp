<?php

namespace Modules\Dormitory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Dormitory\Models\Dormitory;
use Modules\Dormitory\Models\DormitoryRoom;
use Modules\Dormitory\Models\RoomType;

class DormitoryDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $boys = Dormitory::firstOrCreate(['name' => 'Boys Hostel'], ['type' => 'boys', 'address' => 'North Campus']);
        Dormitory::firstOrCreate(['name' => 'Girls Hostel'], ['type' => 'girls', 'address' => 'South Campus']);

        $double = RoomType::firstOrCreate(['name' => 'Non-AC Double']);
        RoomType::firstOrCreate(['name' => 'AC Single']);

        DormitoryRoom::firstOrCreate(
            ['dormitory_id' => $boys->id, 'room_no' => 'B-101'],
            ['room_type_id' => $double->id, 'capacity' => 2, 'cost' => 1500],
        );
        DormitoryRoom::firstOrCreate(
            ['dormitory_id' => $boys->id, 'room_no' => 'B-102'],
            ['room_type_id' => $double->id, 'capacity' => 4, 'cost' => 1000],
        );
    }
}
