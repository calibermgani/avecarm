<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserWorkProfilesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_work_profiles', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('user_id', 20);
			$table->integer('claim_assign_limit');
			$table->integer('caller_benchmark')->nullable();
			$table->integer('role_id');
			$table->timestamps();
			$table->integer('created_by');
			$table->integer('updated_by')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_work_profiles');
	}

}
