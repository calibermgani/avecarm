<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFYIParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('f_y_i_parameters', function (Blueprint $table) {
            $table->increments('id');
            $table->string('fyi_parameter',50);
            $table->string('fyi_sub_parameter',50);
            $table->integer('status');
            $table->integer('created_by');
			$table->integer('updated_by')->nullable();
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
        Schema::dropIfExists('f_y_i_parameters');
    }
}
