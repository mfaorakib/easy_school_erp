<?php

namespace Modules\Behaviour\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Behaviour\Models\BehaviourType;

class BehaviourDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['title' => 'Helping others', 'point' => 5],
            ['title' => 'Excellent work', 'point' => 3],
            ['title' => 'Late to class', 'point' => -2],
            ['title' => 'Disrupting class', 'point' => -5],
        ];

        foreach ($types as $t) {
            BehaviourType::firstOrCreate(['title' => $t['title']], ['point' => $t['point']]);
        }
    }
}
