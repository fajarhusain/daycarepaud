<?php

namespace App\Services;

use App\Models\Sekolah;
use App\Models\SekolahAiPersona;
use App\Support\AiPersonaScope;

class AiPersonaService
{
    public function __construct(
        protected MonevSummaryService $monevSummaryService
    ) {}

    public function getOrCreate(int $sekolahId, string $scope): SekolahAiPersona
    {
        return SekolahAiPersona::query()->firstOrCreate(
            [
                'sekolah_id' => $sekolahId,
                'scope' => $scope,
            ],
            [
                'name' => AiPersonaScope::defaultName($scope),
                'role_title' => AiPersonaScope::defaultRoleTitle($scope),
                'dialog_language' => 'Bahasa Indonesia',
            ]
        );
    }

    /**
     * @return array<string, SekolahAiPersona>
     */
    public function allForSekolah(int $sekolahId): array
    {
        $personas = [];

        foreach (AiPersonaScope::all() as $scope) {
            $personas[$scope] = $this->getOrCreate($sekolahId, $scope);
        }

        return $personas;
    }

    public function resolveActivePrompt(int $sekolahId, string $scope, string $sekolahName): ?string
    {
        return SekolahAiPersona::resolveActivePrompt($sekolahId, $scope, $sekolahName);
    }

    /**
     * @return array<string, mixed>
     */
    public function generate(Sekolah $sekolah, string $scope, ?string $brief = null): array
    {
        $ai = $this->monevSummaryService->resolveAiServiceForSekolah((int) $sekolah->id);

        if ($ai === null) {
            throw new \RuntimeException('AI belum dikonfigurasi. Minta lembaga/yayasan mengisi API Key di Pengaturan AI.');
        }

        return $ai->generatePersonaFields($sekolah->name, $scope, $brief);
    }

    public function isAiConfiguredForSekolah(int $sekolahId): bool
    {
        return $this->monevSummaryService->resolveAiServiceForSekolah($sekolahId) !== null;
    }
}
