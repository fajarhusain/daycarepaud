<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $teamKey = config('permission.column_names.team_foreign_key', 'sekolah_id');

        foreach ([$tableNames['model_has_roles'], $tableNames['model_has_permissions']] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $this->dropPrimary($table);

            if (! Schema::hasColumn($table, 'id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->bigIncrements('id')->first();
                });
            }

            Schema::table($table, function (Blueprint $blueprint) use ($teamKey) {
                $blueprint->unsignedBigInteger($teamKey)->nullable()->change();
            });

            $this->addUnique($table, $teamKey);
        }
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $teamKey = config('permission.column_names.team_foreign_key', 'sekolah_id');

        foreach ([$tableNames['model_has_roles'], $tableNames['model_has_permissions']] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $this->dropUnique($table);

            Schema::table($table, function (Blueprint $blueprint) use ($teamKey) {
                $blueprint->unsignedBigInteger($teamKey)->nullable(false)->change();
            });

            if (Schema::hasColumn($table, 'id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropColumn('id');
                });
            }

            $this->addPrimary($table);
        }
    }

    private function dropPrimary(string $table): void
    {
        $hasPrimary = (bool) DB::selectOne(
            "SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
             AND CONSTRAINT_TYPE = 'PRIMARY KEY' LIMIT 1",
            [$table]
        );

        if ($hasPrimary) {
            DB::statement("ALTER TABLE `{$table}` DROP PRIMARY KEY");
        }
    }

    private function addPrimary(string $table): void
    {
        $tableNames = config('permission.table_names');
        $teamKey = config('permission.column_names.team_foreign_key', 'sekolah_id');
        $isRoles = $table === $tableNames['model_has_roles'];
        $pivotKey = $isRoles
            ? config('permission.column_names.role_pivot_key', 'role_id')
            : config('permission.column_names.permission_pivot_key', 'permission_id');

        $hasPrimary = (bool) DB::selectOne(
            "SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
             AND CONSTRAINT_TYPE = 'PRIMARY KEY' LIMIT 1",
            [$table]
        );

        if ($hasPrimary) {
            return;
        }

        DB::statement(
            "ALTER TABLE `{$table}` ADD PRIMARY KEY (`{$teamKey}`, `{$pivotKey}`, `model_id`, `model_type`)"
        );
    }

    private function addUnique(string $table, string $teamKey): void
    {
        $tableNames = config('permission.table_names');
        $isRoles = $table === $tableNames['model_has_roles'];
        $pivotKey = $isRoles
            ? config('permission.column_names.role_pivot_key', 'role_id')
            : config('permission.column_names.permission_pivot_key', 'permission_id');
        $name = $table.'_unique';

        $exists = collect(Schema::getIndexes($table))->contains(fn ($i) => ($i['name'] ?? '') === $name);
        if ($exists) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($teamKey, $pivotKey, $name) {
            $blueprint->unique([$teamKey, $pivotKey, 'model_id', 'model_type'], $name);
        });
    }

    private function dropUnique(string $table): void
    {
        $name = $table.'_unique';
        $exists = collect(Schema::getIndexes($table))->contains(fn ($i) => ($i['name'] ?? '') === $name);

        if (! $exists) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($name) {
            $blueprint->dropUnique($name);
        });
    }
};
