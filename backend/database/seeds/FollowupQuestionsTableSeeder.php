<?php

use Illuminate\Database\Seeder;

class FollowupQuestionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('followup_questions')->delete();
        
        \DB::table('followup_questions')->insert(array (
            0 => 
            array (
                'id' => 1,
                'question' => 'What\'s the effective date of policy?',
                'question_label' => 'What_s_the_effective_date_of_policy_',
                'hint' => 'EOD Policy',
                'category_id' => 1,
                'field_type' => 'Date',
                'field_validation' => NULL,
                'date_type' => 'single_date',
                'status' => 'Active',
                'created_at' => '2019-03-14 13:55:43',
                'created_by' => 26,
                'updated_at' => '2019-03-14 13:55:43',
                'deleted_at' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'question' => 'What\'s the filing limit?',
                'question_label' => 'What_s_the_filing_limit_',
                'hint' => 'Filing Limit',
                'category_id' => 1,
                'field_type' => 'Number',
                'field_validation' => 'Number',
                'date_type' => NULL,
                'status' => 'Active',
                'created_at' => '2019-03-14 13:56:08',
                'created_by' => 26,
                'updated_at' => '2019-03-14 13:56:08',
                'deleted_at' => NULL,
            ),
        ));
        
        
    }
}