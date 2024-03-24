<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions= [
            ['name' => 'Software Eng'],
            ['name' => 'Software Dev'],
            ['name' => 'Project Management'],
            ['name' => 'Q E'],
            ['name' => 'Intern'],
            
        ];

        
        foreach ($positions as $position) {
            DB::table('positions')->insert($position);
        }
    }
}
