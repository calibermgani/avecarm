<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToNotesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('notes', function(Blueprint $table)
		{
			$table->foreign('claim_id', 'fk_claim_notes')->references('id')->on('claim_infos')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('user', 'fk_user_notes')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('created_by', 'fk_user_notes_created')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('updated_by', 'fk_user_notes_updated')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('notes', function(Blueprint $table)
		{
			$table->dropForeign('fk_claim_notes');
			$table->dropForeign('fk_user_notes');
			$table->dropForeign('fk_user_notes_created');
			$table->dropForeign('fk_user_notes_updated');
		});
	}

}
