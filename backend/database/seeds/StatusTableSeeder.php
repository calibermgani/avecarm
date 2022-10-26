<?php

use Illuminate\Database\Seeder;

class StatusTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('status')->delete();
        
        \DB::table('status')->insert(array (
            0 => 
         array (
                'id' => 1,
                'status_code' => 'CA',
                'description' => 'Client Assistance',
                'status' => 1,
                'created_at' => '2019-05-13 18:13:54',
                'updated_at' => '2019-03-14 11:56:23',
                'created_by' => 26,
                'updated_by' => NULL,
                'deleted_at' => NULL,
                'modules' => '{"followup":true,"audit":true,"ca":true,"rcm":true}',
            ),
            1 => 
			 array (
                'id' => 2,
                'status_code' => 'RCM',
                'description' => 'RCM Team',
                'status' => 1,
                'created_at' => '2019-05-13 18:13:58',
                'updated_at' => '2019-03-14 11:56:46',
                'created_by' => 26,
                'updated_by' => NULL,
                'deleted_at' => NULL,
                'modules' => '{"followup":true,"audit":true,"ca":true,"rcm":true}',
            ),
          
        ));
        
        
    }
}