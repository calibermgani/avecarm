<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePatientDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('patient_details', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('acct_no', 20);
			$table->integer('claim_id')->index('fk_claim_patient');
			$table->string('patient_name', 100);
			$table->dateTime('dob');
			$table->string('ssn', 20);
			$table->enum('gender', array('Male','Female','Others'));
			$table->string('phone_no', 15);
			$table->string('address_line_1', 100);
			$table->string('address_line_2', 100);
			$table->string('city', 20);
			$table->string('state', 20);
			$table->string('zipcode', 10);
			$table->string('gurantor_name', 100);
			$table->string('employer_name', 100);
			$table->timestamps();
			$table->bigInteger('created_by')->index('fk_user_patient_created');
			$table->bigInteger('updated_by')->index('fk_user_patient_updated');
			$table->dateTime('deleted_at');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('patient_details');
	}

}
