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
            $table->string('billed_submit_date',25)->nullable()->after('closed_claim_date');
            $table->string('denial_code')->nullable()->after('billed_submit_date');
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
            $table->dropColumn('billed_submit_date');
            $table->dropColumn('denial_code');
        });
    }
}
