<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBatchUuidColumnToActivityLogTable extends Migration
{
    public function up()
    {
        $tableName = config('activitylog.table_name');
        $tableName = is_string($tableName) ? trim($tableName) : $tableName;
        $tableName = $tableName !== '' && $tableName !== null ? $tableName : 'activity_log';

        Schema::connection(config('activitylog.database_connection'))->table($tableName, function (Blueprint $table) {
            $table->uuid('batch_uuid')->nullable()->after('properties');
        });
    }

    public function down()
    {
        $tableName = config('activitylog.table_name');
        $tableName = is_string($tableName) ? trim($tableName) : $tableName;
        $tableName = $tableName !== '' && $tableName !== null ? $tableName : 'activity_log';

        Schema::connection(config('activitylog.database_connection'))->table($tableName, function (Blueprint $table) {
            $table->dropColumn('batch_uuid');
        });
    }
}
