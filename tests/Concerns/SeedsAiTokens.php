<?php

namespace Tests\Concerns;

use App\Models\Sekolah;
use App\Models\User;
use App\Services\AiTokenService;

trait SeedsAiTokens
{
    protected function seedAiTokens(Sekolah $sekolah, int $amount = 10, ?User $by = null): void
    {
        $by ??= User::factory()->create();

        app(AiTokenService::class)->topUp($sekolah->id, $amount, $by);
    }
}
