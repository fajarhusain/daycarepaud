<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sekolahs', 'status')) {
            Schema::table('sekolahs', function (Blueprint $table) {
                $table->string('status')->default('active')->after('phone');
            });
        }

        if (! Schema::hasColumn('sekolahs', 'slug')) {
            Schema::table('sekolahs', function (Blueprint $table) {
                $after = Schema::hasColumn('sekolahs', 'status') ? 'status' : 'phone';
                $table->string('slug')->nullable()->unique()->after($after);
            });
        }

        if (! Schema::hasColumn('lembagas', 'status')) {
            Schema::table('lembagas', function (Blueprint $table) {
                $table->string('status')->default('active')->after('phone');
            });
        }

        if (! Schema::hasColumn('lembagas', 'slug')) {
            Schema::table('lembagas', function (Blueprint $table) {
                $after = Schema::hasColumn('lembagas', 'status') ? 'status' : 'phone';
                $table->string('slug')->nullable()->unique()->after($after);
            });
        }

        if (! Schema::hasColumn('lembagas', 'contact_name')) {
            Schema::table('lembagas', function (Blueprint $table) {
                $table->string('contact_name')->nullable();
            });
        }

        if (! Schema::hasColumn('lembagas', 'contact_email')) {
            Schema::table('lembagas', function (Blueprint $table) {
                $table->string('contact_email')->nullable();
            });
        }

        if (! Schema::hasColumn('lembagas', 'contact_phone')) {
            Schema::table('lembagas', function (Blueprint $table) {
                $table->string('contact_phone')->nullable();
            });
        }

        if (! Schema::hasColumn('lembagas', 'rejection_reason')) {
            Schema::table('lembagas', function (Blueprint $table) {
                $table->text('rejection_reason')->nullable();
            });
        }

        if (! Schema::hasColumn('lembagas', 'approved_at')) {
            Schema::table('lembagas', function (Blueprint $table) {
                $table->timestamp('approved_at')->nullable();
            });
        }

        if (! Schema::hasColumn('lembagas', 'approved_by')) {
            Schema::table('lembagas', function (Blueprint $table) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }

        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamKey = $columnNames['team_foreign_key'] ?? 'sekolah_id';

        if (! Schema::hasColumn($tableNames['roles'], $teamKey)) {
            Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamKey) {
                $table->unsignedBigInteger($teamKey)->nullable()->after('id');
                $table->index($teamKey, 'roles_'.$teamKey.'_index');
            });
        }

        if (! Schema::hasColumn($tableNames['model_has_roles'], $teamKey)) {
            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamKey) {
                $table->unsignedBigInteger($teamKey)->nullable()->after('role_id');
                $table->index($teamKey, 'model_has_roles_'.$teamKey.'_index');
            });
        }

        if (! Schema::hasColumn($tableNames['model_has_permissions'], $teamKey)) {
            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamKey) {
                $table->unsignedBigInteger($teamKey)->nullable()->after('permission_id');
                $table->index($teamKey, 'model_has_permissions_'.$teamKey.'_index');
            });
        }

        $this->backfillPermissionTeams($tableNames, $teamKey);
    }

    public function down(): void
    {
        $sekolahCols = array_filter(['status', 'slug'], fn ($c) => Schema::hasColumn('sekolahs', $c));
        if ($sekolahCols) {
            Schema::table('sekolahs', function (Blueprint $table) use ($sekolahCols) {
                $table->dropColumn($sekolahCols);
            });
        }

        if (Schema::hasColumn('lembagas', 'approved_by')) {
            Schema::table('lembagas', function (Blueprint $table) {
                $table->dropConstrainedForeignId('approved_by');
            });
        }

        $lembagaCols = array_filter([
            'status', 'slug', 'contact_name', 'contact_email',
            'contact_phone', 'rejection_reason', 'approved_at',
        ], fn ($c) => Schema::hasColumn('lembagas', $c));
        if ($lembagaCols) {
            Schema::table('lembagas', function (Blueprint $table) use ($lembagaCols) {
                $table->dropColumn($lembagaCols);
            });
        }

        $tableNames = config('permission.table_names');
        $teamKey = config('permission.column_names.team_foreign_key', 'sekolah_id');

        foreach ([$tableNames['roles'], $tableNames['model_has_roles'], $tableNames['model_has_permissions']] as $table) {
            if (Schema::hasColumn($table, $teamKey)) {
                Schema::table($table, function (Blueprint $blueprint) use ($teamKey) {
                    $blueprint->dropColumn($teamKey);
                });
            }
        }
    }

    private function backfillPermissionTeams(array $tableNames, string $teamKey): void
    {
        $pivotRole = config('permission.column_names.role_pivot_key') ?? 'role_id';
        $modelKey = config('permission.column_names.model_morph_key') ?? 'model_id';

        $rows = DB::table($tableNames['model_has_roles'])
            ->join('users', 'users.id', '=', $tableNames['model_has_roles'].'.'.$modelKey)
            ->whereNotNull('users.sekolah_id')
            ->whereNull($tableNames['model_has_roles'].'.'.$teamKey)
            ->select([
                $tableNames['model_has_roles'].'.'.$pivotRole.' as role_id',
                $tableNames['model_has_roles'].'.'.$modelKey.' as model_id',
                $tableNames['model_has_roles'].'.model_type as model_type',
                'users.sekolah_id as sekolah_id',
            ])
            ->get();

        foreach ($rows as $row) {
            DB::table($tableNames['model_has_roles'])
                ->where('role_id', $row->role_id)
                ->where('model_id', $row->model_id)
                ->where('model_type', $row->model_type)
                ->whereNull($teamKey)
                ->update([$teamKey => $row->sekolah_id]);
        }
    }
};
