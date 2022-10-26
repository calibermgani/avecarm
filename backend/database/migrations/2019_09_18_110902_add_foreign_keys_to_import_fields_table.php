<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToImportFieldsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('import_fields', function(Blueprint $table)
		{
			$table->foreign('file_upload_id', 'fk_upload_id')->references('id')->on('file_uploads')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('import_fields', function(Blueprint $table)
		{
			$table->dropForeign('fk_upload_id');
		});
	}

}
