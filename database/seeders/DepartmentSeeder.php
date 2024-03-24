<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments= [
            ['name' => 'R&D'],
            ['name' => 'SRE'],
            ['name' => 'SHARED SERVICES-OUTSOURCE'],
            ['name' => 'INFRA-OPS'],
            ['name' => 'SHARED SERVICES'],
        ];

        
        foreach ($departments as $department) {
            DB::table('departments')->insert($department);
        }
    }
}
