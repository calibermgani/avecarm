<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToClaimHistoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('claim_histories', function(Blueprint $table)
		{
			$table->foreign('claim_state', 'claim_state')->references('id')->on('claim_states')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('claim_histories', function(Blueprint $table)
		{
			$table->dropForeign('claim_state');
		});
	}

}
