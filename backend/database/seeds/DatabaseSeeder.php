<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $this->call(FollowupTemplatesTableSeeder::class);
        $this->call(FollowupQuestionsTableSeeder::class);
        $this->call(FollowupCategoriesTableSeeder::class);
        $this->call(ActionTypesTableSeeder::class);
        $this->call(ClaimStatesTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(StatusTableSeeder::class);
    }
}
