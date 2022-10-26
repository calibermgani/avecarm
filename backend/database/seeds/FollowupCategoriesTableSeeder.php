<?php

use Illuminate\Database\Seeder;

class FollowupCategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('followup_categories')->delete();
        
        \DB::table('followup_categories')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Claim NIS',
                'label_name' => 'Claim NIS',
                'status' => 'Active',
                'created_at' => '2019-03-14 13:51:59',
                'created_by' => 26,
                'updated_by' => NULL,
                'updated_at' => '2019-03-14 13:51:59',
                'deleted_at' => NULL,
                'deleted_by' => NULL,
            ),
        ));
        
        
    }
}