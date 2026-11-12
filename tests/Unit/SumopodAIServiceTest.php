<?php

namespace Tests\Unit;

use App\Services\SumopodAIService;
use ReflectionMethod;
use Tests\TestCase;

class SumopodAIServiceTest extends TestCase
{
    private function invoke(SumopodAIService $service, string $method, mixed ...$args): mixed
    {
        $reflection = new ReflectionMethod($service, $method);
        $reflection->setAccessible(true);

        return $reflection->invoke($service, ...$args);
    }

    public function test_parse_json_suggestions_from_plain_json(): void
    {
        $service = new SumopodAIService('test-key');

        $result = $this->invoke($service, 'parseJsonSuggestions', '{"saran":["A","B","C"]}', 'saran');

        $this->assertSame(['A', 'B', 'C'], $result);
    }

    public function test_parse_json_suggestions_from_markdown_fence(): void
    {
        $service = new SumopodAIService('test-key');

        $content = "```json\n{\"saran\":[\"Satu\",\"Dua\"]}\n```";
        $result = $this->invoke($service, 'parseJsonSuggestions', $content, 'saran');

        $this->assertSame(['Satu', 'Dua'], $result);
    }

    public function test_resolve_feedback_suggestions_prefers_json_over_numbered_text(): void
    {
        $service = new SumopodAIService('test-key');

        $content = "Catatan:\n{\"saran\":[\"JSON satu\",\"JSON dua\",\"JSON tiga\"]}\n1. Abaikan ini";
        $result = $this->invoke($service, 'resolveFeedbackSuggestions', $content);

        $this->assertSame(['JSON satu', 'JSON dua', 'JSON tiga'], $result);
    }

    public function test_resolve_feedback_suggestions_falls_back_to_numbered_list(): void
    {
        $service = new SumopodAIService('test-key');

        $content = "1. Saran pertama\n2. Saran kedua\n3. Saran ketiga";
        $result = $this->invoke($service, 'resolveFeedbackSuggestions', $content);

        $this->assertSame(['Saran pertama', 'Saran kedua', 'Saran ketiga'], $result);
    }

    public function test_resolve_feedback_suggestions_throws_on_unparseable_content(): void
    {
        $service = new SumopodAIService('test-key');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Format respons AI tidak dikenali.');

        $this->invoke($service, 'resolveFeedbackSuggestions', 'Hanya paragraf bebas tanpa struktur.');
    }

    public function test_extract_content_from_sse_stream(): void
    {
        $service = new SumopodAIService('test-key');

        $sse = implode("\n", [
            'data: {"choices":[{"delta":{"content":"{\"saran\":[\"Satu\""}}]}',
            'data: {"choices":[{"delta":{"content":",\"Dua\",\"Tiga\"]}"}}]}',
            'data: [DONE]',
        ]);

        $result = $this->invoke($service, 'extractContentFromSse', $sse);
        $suggestions = $this->invoke($service, 'resolveFeedbackSuggestions', $result);

        $this->assertSame(['Satu', 'Dua', 'Tiga'], $suggestions);
    }
}
