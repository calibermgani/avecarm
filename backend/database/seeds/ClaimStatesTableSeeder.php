<?php

use Illuminate\Database\Seeder;

class ClaimStatesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('claim_states')->delete();
        
        \DB::table('claim_states')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Upload',
                'created_at' => '2019-03-13 15:14:18',
                'updated_at' => '2019-03-13 15:14:09',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Overwrite',
                'created_at' => '2019-01-09 17:16:33',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Followup',
                'created_at' => '2019-01-09 17:16:33',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'Audit',
                'created_at' => '2019-01-09 17:17:02',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'Audit Assign',
                'created_at' => '2019-01-09 17:17:20',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'Audit Completion',
                'created_at' => '2019-01-09 17:18:01',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'Client Assistance',
                'created_at' => '2019-01-30 17:49:26',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            7 => 
            array (
                'id' => 8,
                'name' => 'RCM Team',
                'created_at' => '2019-02-11 12:29:51',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            8 => 
            array (
                'id' => 9,
                'name' => 'Closed',
                'created_at' => '2019-07-11 15:50:50',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            9 => 
            array (
                'id' => 10,
                'name' => 'Reimport',
                'created_at' => '2023-03-24 15:50:50',
                'updated_at' => '0000-00-00 00:00:00',
            ),
        ));
        
        
    }
}