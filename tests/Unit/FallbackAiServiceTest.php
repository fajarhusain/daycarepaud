<?php

namespace Tests\Unit;

use App\Services\FallbackAiService;
use App\Services\SumopodAIService;
use Tests\TestCase;

class FallbackAiServiceTest extends TestCase
{
    public function test_uses_first_slot_when_successful(): void
    {
        $first = $this->createMock(SumopodAIService::class);
        $first->expects($this->once())
            ->method('generateFeedbackSuggestions')
            ->willReturn(['satu', 'dua', 'tiga']);

        $second = $this->createMock(SumopodAIService::class);
        $second->expects($this->never())->method('generateFeedbackSuggestions');

        $service = new FallbackAiService([
            $this->slot(1, $first),
            $this->slot(2, $second),
        ]);

        $result = $service->generateFeedbackSuggestions('A', 'B', 'C', 'D');

        $this->assertSame(['satu', 'dua', 'tiga'], $result);
    }

    public function test_falls_back_to_second_slot_when_first_throws(): void
    {
        $first = $this->createMock(SumopodAIService::class);
        $first->method('generateFeedbackSuggestions')
            ->willThrowException(new \RuntimeException('slot 1 down'));

        $second = $this->createMock(SumopodAIService::class);
        $second->expects($this->once())
            ->method('generateFeedbackSuggestions')
            ->willReturn(['backup satu', 'backup dua', 'backup tiga']);

        $service = new FallbackAiService([
            $this->slot(1, $first),
            $this->slot(2, $second),
        ]);

        $result = $service->generateFeedbackSuggestions('A', 'B', 'C', 'D');

        $this->assertSame(['backup satu', 'backup dua', 'backup tiga'], $result);
    }

    public function test_throws_last_error_when_all_slots_fail(): void
    {
        $first = $this->createMock(SumopodAIService::class);
        $first->method('chatCompletion')
            ->willThrowException(new \RuntimeException('first failed'));

        $second = $this->createMock(SumopodAIService::class);
        $second->method('chatCompletion')
            ->willThrowException(new \RuntimeException('second failed'));

        $service = new FallbackAiService([
            $this->slot(1, $first),
            $this->slot(2, $second),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('second failed');

        $service->chatCompletion([['role' => 'user', 'content' => 'hi']]);
    }

    public function test_run_with_meta_reports_successful_slot(): void
    {
        $first = $this->createMock(SumopodAIService::class);
        $first->method('generateFeedbackSuggestions')
            ->willThrowException(new \RuntimeException('fail'));

        $second = $this->createMock(SumopodAIService::class);
        $second->method('generateFeedbackSuggestions')
            ->willReturn(['ok']);

        $service = new FallbackAiService([
            $this->slot(1, $first, 'Slot 1 (Utama)', 'SumoPod AI', 'gpt-a'),
            $this->slot(2, $second, 'Slot 2 (Backup 1)', 'Custom', 'gpt-b'),
        ]);

        [$result, $meta] = $service->runWithMeta(
            fn (SumopodAIService $ai) => $ai->generateFeedbackSuggestions('A', 'B', 'C', 'D')
        );

        $this->assertSame(['ok'], $result);
        $this->assertSame(2, $meta['slot']);
        $this->assertSame('Slot 2 (Backup 1)', $meta['label']);
        $this->assertSame('Custom', $meta['provider']);
        $this->assertSame('gpt-b', $meta['model']);
    }

    /**
     * @return array{slot: int, label: string, provider: string, model: string, service: SumopodAIService}
     */
    private function slot(
        int $n,
        SumopodAIService $service,
        ?string $label = null,
        string $provider = 'Test',
        string $model = 'test-model'
    ): array {
        return [
            'slot' => $n,
            'label' => $label ?? 'Slot '.$n,
            'provider' => $provider,
            'model' => $model,
            'service' => $service,
        ];
    }
}
