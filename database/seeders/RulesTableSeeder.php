<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rules')->insert([
            [
                'name' => 'sector manager',
                'permission_ids' => '1,9,10,11,35,40,41,48,49,51,52,54,55,56,82,83,84,85,86,87,88,89,90,91,92,93,1181,2,3,4,5,6,7,8,9,10,11,13,14,15,16,17,18,19,21,22,23,32,33,34,35,37,38,39,40,41,42,44,46,47,48,49,51,52,53,54,55,56,57,58,59,60,61,62,63,64,66,73,74,75,76,77,78,79,80,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,120,121,122,123,124,125,126,127,128,129,130,131,132,133,134,135,136,137,138,139,140,141,142,143,144,145',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Add more rules as needed
        ]);
    }
}
