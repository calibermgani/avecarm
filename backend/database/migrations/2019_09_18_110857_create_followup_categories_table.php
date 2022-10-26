<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFollowupCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('followup_categories', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name', 120);
			$table->string('label_name', 120);
			$table->enum('status', array('Active','Inactive'));
			$table->timestamps();
			$table->integer('created_by');
			$table->integer('updated_by')->nullable();
			$table->softDeletes();
			$table->integer('deleted_by')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('followup_categories');
	}

}
