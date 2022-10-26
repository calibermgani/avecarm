<?php

use Illuminate\Database\Seeder;

class ActionTypesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('action_types')->delete();
        
        \DB::table('action_types')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Followup',
                'created_at' => '2019-06-20 18:36:27',
                'created_by' => NULL,
                'updated_at' => NULL,
                'deleted_at' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Audit',
                'created_at' => '2019-06-20 18:36:36',
                'created_by' => NULL,
                'updated_at' => NULL,
                'deleted_at' => NULL,
            ),
        ));
        
        
    }
}