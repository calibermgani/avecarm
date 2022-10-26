<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserLoginHistorysTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_login_historys', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('session_id');
			$table->string('ip_address', 25);
			$table->string('login_time', 25);
			$table->string('logout_time', 25);
			$table->bigInteger('user_id')->index('fk_user_history');
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_login_historys');
	}

}
