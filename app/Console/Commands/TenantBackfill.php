<?php

namespace App\Console\Commands;

use App\Models\Lembaga;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class TenantBackfill extends Command
{
    protected $signature = 'tenant:backfill';

    protected $description = 'Backfill tenant data: ortu sekolah_id null, lembaga/sekolah status active, permission teams';

    public function handle(): int
    {
        $this->info('Backfilling orang tua sekolah_id → null...');
        $ortuRole = Role::where('name', 'Orang Tua')->first();
        if ($ortuRole) {
            $ortuUserIds = DB::table('model_has_roles')
                ->where('role_id', $ortuRole->id)
                ->pluck('model_id');
            User::whereIn('id', $ortuUserIds)->update(['sekolah_id' => null]);
        }

        $this->info('Setting lembaga & sekolah status → active...');
        Lembaga::whereNull('status')->orWhere('status', '')->update(['status' => Lembaga::STATUS_ACTIVE]);
        Sekolah::whereNull('status')->orWhere('status', '')->update(['status' => Sekolah::STATUS_ACTIVE]);

        $this->info('Backfilling model_has_roles.sekolah_id...');
        $teamKey = config('permission.column_names.team_foreign_key', 'sekolah_id');
        $table = config('permission.table_names.model_has_roles');

        $rows = DB::table($table)
            ->join('users', 'users.id', '=', $table.'.model_id')
            ->whereNotNull('users.sekolah_id')
            ->whereNull($table.'.'.$teamKey)
            ->select([
                $table.'.role_id',
                $table.'.model_id',
                $table.'.model_type',
                'users.sekolah_id',
            ])
            ->get();

        foreach ($rows as $row) {
            DB::table($table)
                ->where('role_id', $row->role_id)
                ->where('model_id', $row->model_id)
                ->where('model_type', $row->model_type)
                ->whereNull($teamKey)
                ->update([$teamKey => $row->sekolah_id]);
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
