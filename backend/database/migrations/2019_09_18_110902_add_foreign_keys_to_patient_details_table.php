<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPatientDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('patient_details', function(Blueprint $table)
		{
			$table->foreign('claim_id', 'fk_claim_patient')->references('id')->on('claim_infos')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('created_by', 'fk_user_patient_created')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('updated_by', 'fk_user_patient_updated')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('patient_details', function(Blueprint $table)
		{
			$table->dropForeign('fk_claim_patient');
			$table->dropForeign('fk_user_patient_created');
			$table->dropForeign('fk_user_patient_updated');
		});
	}

}
