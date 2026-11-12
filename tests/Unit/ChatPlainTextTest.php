<?php

namespace Tests\Unit;

use App\Support\ChatPlainText;
use PHPUnit\Framework\TestCase;

class ChatPlainTextTest extends TestCase
{
    public function test_strips_common_markdown_formatting(): void
    {
        $input = "**Anak Chat** berkembang dengan _baik_.\n\n# Ringkasan\n\n[Lihat](https://example.com)";

        $output = ChatPlainText::fromMarkdown($input);

        $this->assertSame("Anak Chat berkembang dengan baik.\n\nRingkasan\n\nLihat", $output);
    }
}
