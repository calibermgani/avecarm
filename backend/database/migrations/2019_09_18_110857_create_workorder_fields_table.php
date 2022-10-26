<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWorkorderFieldsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('workorder_fields', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('work_order_type');
			$table->string('work_order_name', 30);
			$table->date('due_date');
			$table->string('status', 20);
			$table->string('priority', 20);
			$table->text('work_notes', 65535);
			$table->integer('created_by');
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->date('updated_at')->nullable();
			$table->date('deleted_at')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('workorder_fields');
	}

}
