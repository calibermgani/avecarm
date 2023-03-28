<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParentParametersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('parent_parameters')->delete();
        
        DB::table('parent_parameters')->insert(array (
            0 => array (
                    'id' => 1,
                    'err_params' => 'Analysis',
                    'created_at' => '2023-03-28 15:14:18',
                    'updated_at' => '2023-03-28 15:14:18',
                    'status' => 1,
                    'created_by' => 1,
                    'updated_by' => NULL
                ),
            1 => array (
                    'id' => 2,
                    'err_params' => 'Calling',
                    'created_at' => '2023-03-28 15:14:18',
                    'updated_at' => '2023-03-28 15:14:18',
                    'status' => 1,
                    'created_by' => 1,
                    'updated_by' => NULL
                ),
            2 => array (
                    'id' => 3,
                    'err_params' => 'Documentation',
                    'created_at' => '2023-03-28 15:14:18',
                    'updated_at' => '2023-03-28 15:14:18',
                    'status' => 1,
                    'created_by' => 1,
                    'updated_by' => NULL
                ),
            3 => array (
                    'id' => 4,
                    'err_params' => 'Action',
                    'created_at' => '2023-03-28 15:14:18',
                    'updated_at' => '2023-03-28 15:14:18',
                    'status' => 1,
                    'created_by' => 1,
                    'updated_by' => NULL
            ),
            4 => array (
                    'id' => 5,
                    'err_params' => 'FYI',
                    'created_at' => '2023-03-28 15:14:18',
                    'updated_at' => '2023-03-28 15:14:18',
                    'status' => 1,
                    'created_by' => 1,
                    'updated_by' => NULL
            ),
            5 => array (
                    'id' => 6,
                    'err_params' => 'Rework',
                    'created_at' => '2023-03-28 15:14:18',
                    'updated_at' => '2023-03-28 15:14:18',
                    'status' => 1,
                    'created_by' => 1,
                    'updated_by' => NULL
            ),
        ));
    }
}
