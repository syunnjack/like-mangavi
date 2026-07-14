<?php

namespace Tests\Unit;

use App\Support\MangaTagger;
use PHPUnit\Framework\TestCase;

class MangaTaggerTest extends TestCase
{
    public function test_extracts_tags_from_title_and_caption(): void
    {
        $tags = MangaTagger::extract('アニメ化決定の話題作', '完結済み全10巻');

        $this->assertContains('アニメ化', $tags);
        $this->assertContains('完結済み', $tags);
    }

    public function test_returns_empty_array_when_no_keywords_match(): void
    {
        $tags = MangaTagger::extract('ふつうの作品', '');

        $this->assertSame([], $tags);
    }

    public function test_does_not_duplicate_tags(): void
    {
        $tags = MangaTagger::extract('完結 完結済み', '');

        $this->assertSame(['完結済み'], $tags);
    }
}
