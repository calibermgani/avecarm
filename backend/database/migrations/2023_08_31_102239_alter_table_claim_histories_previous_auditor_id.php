<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableClaimHistoriesPreviousAuditorId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claim_histories', function (Blueprint $table) {
            $table->integer('previous_auditor_id')->nullable()->after('assigned_to');
            $table->integer('previous_audit_mgr_id')->nullable()->after('previous_auditor_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claim_histories', function (Blueprint $table) {
            $table->dropColumn('previous_auditor_id');
            $table->dropColumn('previous_audit_mgr_id');
        });
    }
}
