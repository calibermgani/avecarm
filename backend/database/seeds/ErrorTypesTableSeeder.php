<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ErrorTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('error_types')->delete();
        
        DB::table('error_types')->insert(array (
            0 => array (
                    'id' => 1,
                    'name' => 'No Error',
                    'status' => 1,
                    'created_at' => '2023-03-28 15:14:18',
                    'updated_at' => '2023-03-28 15:14:18',
                ),
            1 => array (
                    'id' => 2,
                    'name' => 'Error',
                    'status' => 1,
                    'created_at' => '2023-03-28 15:14:18',
                    'updated_at' => '2023-03-28 15:14:18',
                ),
            2 => array (
                    'id' => 3,
                    'name' => 'FYI',
                    'status' => 1,
                    'created_at' => '2023-03-28 15:14:18',
                    'updated_at' => '2023-03-28 15:14:18',
                ),
            3 => array (
                    'id' => 4,
                    'name' => 'Clarification',
                    'status' => 1,
                    'created_at' => '2023-03-28 15:14:18',
                    'updated_at' => '2023-03-28 15:14:18',
            ),
        ));
    }
}
