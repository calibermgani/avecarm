<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateClientNotesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('client_notes', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('claim_id', 20);
			$table->enum('state', array('Active','Inactive','Edited'));
			$table->text('content', 65535);
			$table->timestamps();
			$table->bigInteger('created_by');
			$table->bigInteger('updated_by')->nullable();
			$table->softDeletes()->default(DB::raw('CURRENT_TIMESTAMP'));
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('client_notes');
	}

}
