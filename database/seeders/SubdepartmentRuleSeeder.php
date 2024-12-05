<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubdepartmentRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rules')->insert([
            [
                'name' => 'subdepartment manager',
                'permission_ids' => '1,2,3,12,4,5,6,7,8,9,10,11,13,14,15,16,17,18,19,48,20,21,46,22,23,24,25,26,27,28,29,47,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,49,50,51,52,53,54,55',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}
