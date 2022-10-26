<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateClaimHistoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('claim_histories', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('claim_id', 20);
			$table->integer('claim_state')->index('claim_state');
			$table->integer('assigned_by')->nullable();
			$table->integer('assigned_to')->nullable();
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('claim_histories');
	}

}
