<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableQcNotesAddParametersColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qc_notes', function (Blueprint $table) {
            $table->string('parameter',255)->nullable()->after('root_cause');
            $table->string('sub_parameter',255)->nullable()->after('parameter');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qc_notes', function (Blueprint $table) {
            $table->dropColumn('parameter');
            $table->dropColumn('sub_parameter');
        });
    }
}
