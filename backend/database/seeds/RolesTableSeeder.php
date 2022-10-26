<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('roles')->delete();
        
        \DB::table('roles')->insert(array (
            0 => 
            array (
                'id' => 1,
                'role_name' => 'AR Executive',
                'status' => 'Active',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2019-03-29 17:37:18',
                'updated_at' => '2019-03-29 17:37:18',
                'deleted_at' => '2019-03-29 17:37:18',
            ),
            1 => 
            array (
                'id' => 2,
                'role_name' => 'TL / Group Coordinator',
                'status' => 'Active',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2018-09-11 18:43:49',
                'updated_at' => '0000-00-00 00:00:00',
                'deleted_at' => '0000-00-00 00:00:00',
            ),
            2 => 
            array (
                'id' => 3,
                'role_name' => 'AM and Managers',
                'status' => 'Active',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2018-09-11 18:43:49',
                'updated_at' => '0000-00-00 00:00:00',
                'deleted_at' => '0000-00-00 00:00:00',
            ),
            3 => 
            array (
                'id' => 4,
                'role_name' => 'Auditor',
                'status' => 'Active',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2019-03-29 17:34:59',
                'updated_at' => '2019-03-29 17:34:59',
                'deleted_at' => '2019-03-29 17:34:59',
            ),
            4 => 
            array (
                'id' => 5,
                'role_name' => 'Administrator',
                'status' => 'Active',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2018-09-11 18:43:49',
                'updated_at' => '0000-00-00 00:00:00',
                'deleted_at' => '0000-00-00 00:00:00',
            ),
            5 => 
            array (
                'id' => 6,
                'role_name' => 'QC User ',
                'status' => 'Active',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2019-01-30 13:45:24',
                'updated_at' => '0000-00-00 00:00:00',
                'deleted_at' => '0000-00-00 00:00:00',
            ),
            6 => 
            array (
                'id' => 7,
                'role_name' => 'RCM Team',
                'status' => 'Active',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => '2019-02-07 13:31:37',
                'updated_at' => '0000-00-00 00:00:00',
                'deleted_at' => '0000-00-00 00:00:00',
            ),
        ));
        
        
    }
}