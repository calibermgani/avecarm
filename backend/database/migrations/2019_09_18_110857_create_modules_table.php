<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateModulesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('modules', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('module_name', 20);
			$table->integer('parent_module_id')->index('fk_module_module');
			$table->timestamps();
			$table->bigInteger('created_by')->index('fk_user_module_create');
			$table->bigInteger('updated_by')->index('fk_user_module_update');
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
		Schema::drop('modules');
	}

}
