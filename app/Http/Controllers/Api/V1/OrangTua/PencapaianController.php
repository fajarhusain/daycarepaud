<?php

namespace App\Http\Controllers\Api\V1\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Pencapaian;
use App\Support\LabelSkorPencapaian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PencapaianController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $anakIds = Anak::query()
            ->where('user_id', $request->user()->id)
            ->pluck('id');

        $query = Pencapaian::query()
            ->with(['anak:id,name,sekolah_id', 'kegiatan:id,title,date', 'matrikulasi:id,aspek,indicator', 'pengajar:id,name'])
            ->whereIn('anak_id', $anakIds)
            ->orderByDesc('updated_at');

        $paginator = $query->paginate((int) $request->input('per_page', 20));

        $paginator->getCollection()->transform(function (Pencapaian $p) {
            $sekolahId = $p->anak?->sekolah_id;

            return [
                'id' => $p->id,
                'score' => $p->score,
                'score_label' => LabelSkorPencapaian::label($p->score, $sekolahId ? (int) $sekolahId : null),
                'feedback' => $p->feedback,
                'updated_at' => $p->updated_at?->toIso8601String(),
                'anak' => $p->anak ? ['id' => $p->anak->id, 'name' => $p->anak->name] : null,
                'kegiatan' => $p->kegiatan ? [
                    'id' => $p->kegiatan->id,
                    'title' => $p->kegiatan->title,
                    'date' => $p->kegiatan->date,
                ] : null,
                'matrikulasi' => $p->matrikulasi ? [
                    'aspek' => $p->matrikulasi->aspek,
                    'indicator' => $p->matrikulasi->indicator,
                ] : null,
                'pengajar' => $p->pengajar ? ['name' => $p->pengajar->name] : null,
            ];
        });

        return response()->json($paginator);
    }
}
