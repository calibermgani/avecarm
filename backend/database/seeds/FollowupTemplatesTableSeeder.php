<?php

use Illuminate\Database\Seeder;

class FollowupTemplatesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('followup_templates')->delete();
        
        \DB::table('followup_templates')->insert(array (
            0 => 
            array (
                'id' => 24,
                'claim_id' => '25550',
                'rep_name' => 'test',
                'date' => '2019-03-14',
                'phone' => '123654',
                'insurance_id' => 25,
                'category_id' => 1,
                'content' => '[{"question":"What\'s the effective date of policy?","hint":"EOD Policy","question_label":"What_s_the_effective_date_of_policy_","type":"date","answer":"2019-03-14"},{"question":"What\'s the filing limit?","hint":"Filing Limit","question_label":"What_s_the_filing_limit_","answer":"123"}]',
                'created_by' => 27,
                'created_at' => '2019-03-14 17:33:45',
                'updated_at' => '2019-03-14 17:33:45',
                'deleted_at' => NULL,
            ),
            1 => 
            array (
                'id' => 25,
                'claim_id' => '25550',
                'rep_name' => 'test2',
                'date' => '2019-03-14',
                'phone' => '1147852369',
                'insurance_id' => 25,
                'category_id' => 1,
                'content' => '[{"question":"What\'s the effective date of policy?","hint":"EOD Policy","question_label":"What_s_the_effective_date_of_policy_","type":"date","answer":"2019-03-14"},{"question":"What\'s the filing limit?","hint":"Filing Limit","question_label":"What_s_the_filing_limit_","answer":"1236"}]',
                'created_by' => 27,
                'created_at' => '2019-03-14 17:34:44',
                'updated_at' => '2019-03-14 17:34:44',
                'deleted_at' => NULL,
            ),
            2 => 
            array (
                'id' => 26,
                'claim_id' => '25575',
                'rep_name' => 'test',
                'date' => '2019-03-14',
                'phone' => '12345',
                'insurance_id' => 25,
                'category_id' => 1,
                'content' => '[{"question":"What\'s the effective date of policy?","hint":"EOD Policy","question_label":"What_s_the_effective_date_of_policy_","type":"date","answer":"2019-03-14"},{"question":"What\'s the filing limit?","hint":"Filing Limit","question_label":"What_s_the_filing_limit_","answer":"456"}]',
                'created_by' => 26,
                'created_at' => '2019-03-14 17:36:11',
                'updated_at' => '2019-03-14 17:36:11',
                'deleted_at' => NULL,
            ),
            3 => 
            array (
                'id' => 27,
                'claim_id' => '25575',
                'rep_name' => 'test2',
                'date' => '2019-03-14',
                'phone' => '12345',
                'insurance_id' => 25,
                'category_id' => 1,
                'content' => '[{"question":"What\'s the effective date of policy?","hint":"EOD Policy","question_label":"What_s_the_effective_date_of_policy_","type":"date","answer":"2019-03-14"},{"question":"What\'s the filing limit?","hint":"Filing Limit","question_label":"What_s_the_filing_limit_","answer":"56465"}]',
                'created_by' => 26,
                'created_at' => '2019-03-14 17:38:16',
                'updated_at' => '2019-03-14 17:38:16',
                'deleted_at' => NULL,
            ),
            4 => 
            array (
                'id' => 28,
                'claim_id' => '25575',
                'rep_name' => 'test3',
                'date' => '2019-03-14',
                'phone' => '12314564',
                'insurance_id' => 25,
                'category_id' => 1,
                'content' => '[{"question":"What\'s the effective date of policy?","hint":"EOD Policy","question_label":"What_s_the_effective_date_of_policy_","type":"date","answer":"2019-03-14"},{"question":"What\'s the filing limit?","hint":"Filing Limit","question_label":"What_s_the_filing_limit_","answer":"46"}]',
                'created_by' => 26,
                'created_at' => '2019-03-14 17:46:50',
                'updated_at' => '2019-03-14 17:46:50',
                'deleted_at' => NULL,
            ),
            5 => 
            array (
                'id' => 29,
                'claim_id' => '25550',
                'rep_name' => 'test3',
                'date' => '2019-03-14',
                'phone' => '5644',
                'insurance_id' => 25,
                'category_id' => 1,
                'content' => '[{"question":"What\'s the effective date of policy?","hint":"EOD Policy","question_label":"What_s_the_effective_date_of_policy_","type":"date","answer":"2019-03-14"},{"question":"What\'s the filing limit?","hint":"Filing Limit","question_label":"What_s_the_filing_limit_","answer":"6574"}]',
                'created_by' => 27,
                'created_at' => '2019-03-14 17:47:15',
                'updated_at' => '2019-03-14 17:47:15',
                'deleted_at' => NULL,
            ),
            6 => 
            array (
                'id' => 30,
                'claim_id' => '25550',
                'rep_name' => 'test4',
                'date' => '2019-03-14',
                'phone' => '87945',
                'insurance_id' => 25,
                'category_id' => 1,
                'content' => '[{"question":"What\'s the effective date of policy?","hint":"EOD Policy","question_label":"What_s_the_effective_date_of_policy_","type":"date","answer":"2019-03-14"},{"question":"What\'s the filing limit?","hint":"Filing Limit","question_label":"What_s_the_filing_limit_","answer":"10"}]',
                'created_by' => 27,
                'created_at' => '2019-03-14 17:53:07',
                'updated_at' => '2019-03-14 17:53:07',
                'deleted_at' => NULL,
            ),
            7 => 
            array (
                'id' => 31,
                'claim_id' => '25550',
                'rep_name' => 'tesd5',
                'date' => '2019-03-14',
                'phone' => '123445',
                'insurance_id' => 25,
                'category_id' => 1,
                'content' => '[{"question":"What\'s the effective date of policy?","hint":"EOD Policy","question_label":"What_s_the_effective_date_of_policy_","type":"date","answer":"2019-03-14"},{"question":"What\'s the filing limit?","hint":"Filing Limit","question_label":"What_s_the_filing_limit_","answer":"452"}]',
                'created_by' => 27,
                'created_at' => '2019-03-14 17:55:55',
                'updated_at' => '2019-03-14 17:55:55',
                'deleted_at' => NULL,
            ),
            8 => 
            array (
                'id' => 32,
                'claim_id' => '25550',
                'rep_name' => 'tesa',
                'date' => '2019-03-14',
                'phone' => '456456',
                'insurance_id' => 25,
                'category_id' => 1,
                'content' => '[{"question":"What\'s the effective date of policy?","hint":"EOD Policy","question_label":"What_s_the_effective_date_of_policy_","type":"date","answer":"2019-03-14"},{"question":"What\'s the filing limit?","hint":"Filing Limit","question_label":"What_s_the_filing_limit_","answer":"452"}]',
                'created_by' => 27,
                'created_at' => '2019-03-14 17:56:48',
                'updated_at' => '2019-03-14 17:56:48',
                'deleted_at' => NULL,
            ),
            9 => 
            array (
                'id' => 33,
                'claim_id' => '25550',
                'rep_name' => 'rew',
                'date' => '2019-03-14',
                'phone' => '786786',
                'insurance_id' => 25,
                'category_id' => 1,
                'content' => '[{"question":"What\'s the effective date of policy?","hint":"EOD Policy","question_label":"What_s_the_effective_date_of_policy_","type":"date","answer":"2019-03-14"},{"question":"What\'s the filing limit?","hint":"Filing Limit","question_label":"What_s_the_filing_limit_","answer":"786"}]',
                'created_by' => 27,
                'created_at' => '2019-03-14 17:58:22',
                'updated_at' => '2019-03-14 17:58:22',
                'deleted_at' => NULL,
            ),
            10 => 
            array (
                'id' => 34,
                'claim_id' => '25541',
                'rep_name' => 'Test',
                'date' => '2019-04-03',
                'phone' => '56465456464',
                'insurance_id' => 25,
                'category_id' => 1,
                'content' => '[{"question":"What\'s the effective date of policy?","hint":"EOD Policy","question_label":"What_s_the_effective_date_of_policy_","type":"date","answer":"2019-04-03"},{"question":"What\'s the filing limit?","hint":"Filing Limit","question_label":"What_s_the_filing_limit_","answer":"452"}]',
                'created_by' => 27,
                'created_at' => '2019-04-03 11:04:38',
                'updated_at' => '2019-04-03 11:04:38',
                'deleted_at' => NULL,
            ),
        ));
        
        
    }
}