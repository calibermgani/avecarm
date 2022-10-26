<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSubStatusTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sub_status', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('status_code', 20);
			$table->integer('parent_status_id')->nullable()->index('fk_substatus_status');
			$table->string('description', 100);
			$table->timestamps();
			$table->bigInteger('created_by')->index('fk_user_claim_create');
			$table->bigInteger('updated_by')->nullable()->index('fk_user_claim_update');
			$table->softDeletes();
			$table->integer('status');
			$table->text('modules', 65535);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sub_status');
	}

}
