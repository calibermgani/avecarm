<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableImportFieldsAddColumnClosedClaimDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import_fields', function (Blueprint $table) {
            $table->date('closed_claim_date')->nullable()->after('claim_Status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('import_fields', function (Blueprint $table) {
            $table->dropColumn('closed_claim_date');
        });
    }
}
