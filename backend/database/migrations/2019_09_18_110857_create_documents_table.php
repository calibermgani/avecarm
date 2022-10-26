<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDocumentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('documents', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('document_name', 20)->nullable();
			$table->string('category', 30);
			$table->text('file_name', 65535)->nullable();
			$table->text('uploaded_name', 65535)->nullable();
			$table->text('archived', 65535)->nullable();
			$table->timestamps();
			$table->integer('created_by');
			$table->integer('updated_by')->nullable();
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('documents');
	}

}
