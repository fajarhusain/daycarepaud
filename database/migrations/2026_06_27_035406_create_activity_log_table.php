<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogTable extends Migration
{
    public function up()
    {
        $tableName = config('activitylog.table_name');
        $tableName = is_string($tableName) ? trim($tableName) : $tableName;
        $tableName = $tableName !== '' && $tableName !== null ? $tableName : 'activity_log';

        Schema::connection(config('activitylog.database_connection'))->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->timestamps();
            $table->index('log_name');
        });
    }

    public function down()
    {
        $tableName = config('activitylog.table_name');
        $tableName = is_string($tableName) ? trim($tableName) : $tableName;
        $tableName = $tableName !== '' && $tableName !== null ? $tableName : 'activity_log';

        Schema::connection(config('activitylog.database_connection'))->dropIfExists($tableName);
    }
}
