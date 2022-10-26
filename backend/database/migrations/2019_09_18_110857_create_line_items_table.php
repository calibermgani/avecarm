<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLineItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('line_items', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('claim_id', 100);
			$table->string('total_ar_due', 20)->nullable();
			$table->string('ins_ar', 20)->nullable();
			$table->string('pat_ar', 20)->nullable();
			$table->string('units', 10)->nullable();
			$table->string('modifier', 50)->nullable();
			$table->string('icd', 10)->nullable();
			$table->string('cpt', 20)->nullable();
			$table->string('dos', 20)->nullable();
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
		Schema::drop('line_items');
	}

}
