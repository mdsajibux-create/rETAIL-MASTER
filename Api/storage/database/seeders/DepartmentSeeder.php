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
    public function run()
    {
        $departments = [];
        $now = now();
        for ($i = 1; $i <= 10; $i++) {
            $departments[] = [
                'name' => "Department $i",
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('departments')->insert($departments);
    }
}
