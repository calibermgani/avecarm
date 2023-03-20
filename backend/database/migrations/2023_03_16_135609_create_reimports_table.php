<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReimportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reimports', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('report_date');
            $table->string('file_name');
			$table->text('unique_name');
			$table->string('file_url');
			$table->string('notes', 500);
			$table->integer('total_claims');
			$table->bigInteger('reimport_by');
			$table->integer('claims_processed');
			$table->enum('status', array('Complete','Incomplete'))->default('Incomplete');
			$table->timestamps();
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
        Schema::dropIfExists('reimports');
    }
}
