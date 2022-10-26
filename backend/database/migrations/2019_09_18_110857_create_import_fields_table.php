<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImportFieldsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('import_fields', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('file_upload_id')->index('fk_upload_id');
			$table->string('acct_no', 20);
			$table->string('claim_no', 100);
			$table->string('patient_name', 100)->nullable();
			$table->string('dos', 20);
			$table->string('dob', 20);
			$table->string('ssn', 20)->nullable();
			$table->string('gender', 20)->nullable();
			$table->string('phone_no', 20)->nullable();
			$table->string('address_1', 100)->nullable();
			$table->string('address_2', 100)->nullable();
			$table->string('city', 30)->nullable();
			$table->string('state', 20)->nullable();
			$table->string('zipcode', 10)->nullable();
			$table->string('guarantor', 20)->nullable();
			$table->string('employer', 100)->nullable();
			$table->string('responsibility', 100)->nullable();
			$table->string('insurance_type', 100)->nullable();
			$table->string('prim_ins_name', 100)->nullable();
			$table->string('prim_pol_id', 50)->nullable();
			$table->string('prim_group_id', 50)->nullable();
			$table->string('prim_address_1', 100)->nullable();
			$table->string('prim_address_2', 100)->nullable();
			$table->string('prim_city', 30)->nullable();
			$table->string('prim_state', 20)->nullable();
			$table->string('prim_zipcode', 10)->nullable();
			$table->string('sec_ins_name', 50)->nullable();
			$table->string('sec_pol_id', 50)->nullable();
			$table->string('sec_group_id', 50)->nullable();
			$table->string('sec_address_1', 100)->nullable();
			$table->string('sec_address_2', 100)->nullable();
			$table->string('sec_city', 30)->nullable();
			$table->string('sec_state', 20)->nullable();
			$table->string('sec_zipcode', 10)->nullable();
			$table->string('ter_ins_name', 50)->nullable();
			$table->string('ter_pol_id', 50)->nullable();
			$table->string('ter_group_id', 50)->nullable();
			$table->string('ter_address_1', 100)->nullable();
			$table->string('ter_address_2', 100)->nullable();
			$table->string('ter_city', 30)->nullable();
			$table->string('ter_state', 20)->nullable();
			$table->string('ter_zipcode', 10)->nullable();
			$table->string('auth_no', 50)->nullable();
			$table->string('rendering_prov', 50)->nullable();
			$table->string('billing_prov', 50)->nullable();
			$table->string('facility', 100)->nullable();
			$table->string('admit_date', 20);
			$table->string('discharge_date', 20);
			$table->string('cpt', 20)->nullable();
			$table->string('icd', 10)->nullable();
			$table->string('modifiers', 50)->nullable();
			$table->string('units', 10)->nullable();
			$table->string('total_charges', 20)->nullable();
			$table->string('pat_ar', 20)->nullable();
			$table->string('ins_ar', 20)->nullable();
			$table->string('total_ar', 20)->nullable();
			$table->string('claim_Status', 20)->nullable();
			$table->string('claim_note', 500)->nullable();
			$table->timestamps();
			$table->integer('assigned_to')->nullable();
			$table->integer('status_code')->nullable();
			$table->integer('substatus_code')->nullable();
			$table->integer('followup_associate')->nullable();
			$table->date('followup_date')->nullable();
			$table->integer('followup_work_order')->nullable();
			$table->integer('audit_work_order')->nullable();
			$table->integer('ca_work_order')->nullable();
			$table->integer('rcm_work_order')->nullable();
			$table->integer('claim_closing')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('import_fields');
	}

}
