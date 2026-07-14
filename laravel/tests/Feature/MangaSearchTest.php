<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MangaSearchTest extends TestCase
{
    use RefreshDatabase;

    private function fakeBooksAndIchiba(array $items): void
    {
        Http::fake([
            'openapi.rakuten.co.jp/services/api/*' => Http::response(['Items' => $items], 200),
            'openapi.rakuten.co.jp/ichibams/*' => Http::response(['Items' => []], 200),
        ]);
    }

    public function test_index_shows_search_form_and_genres(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('異世界');
        $response->assertSee('BL');
    }

    public function test_search_without_keyword_redirects_to_index(): void
    {
        $response = $this->get('/search');

        $response->assertRedirect(route('manga.index'));
    }

    public function test_search_renders_manga_returned_by_rakuten(): void
    {
        $this->fakeBooksAndIchiba([
            [
                'isbn' => '9784088821559',
                'title' => 'テストマンガ 1巻',
                'author' => 'テスト作者',
                'itemPrice' => 500,
                'itemUrl' => 'https://books.rakuten.co.jp/rb/1/',
            ],
        ]);

        $response = $this->get('/search?keyword=' . urlencode('テスト'));

        $response->assertStatus(200);
        $response->assertSee('テストマンガ 1巻');
    }

    public function test_search_shows_empty_message_when_nothing_found(): void
    {
        $this->fakeBooksAndIchiba([]);

        $response = $this->get('/search?keyword=' . urlencode('存在しない作品'));

        $response->assertStatus(200);
        $response->assertSee('見つかりませんでした');
    }

    public function test_search_handles_api_failure_gracefully(): void
    {
        Http::fake([
            'openapi.rakuten.co.jp/*' => Http::response(null, 500),
        ]);

        $response = $this->get('/search?keyword=' . urlencode('テスト'));

        $response->assertStatus(200);
        $response->assertSee('見つかりませんでした');
    }

    public function test_photobook_category_uses_genre_id_and_shows_category_label(): void
    {
        $this->fakeBooksAndIchiba([
            ['isbn' => '1111111111111', 'title' => 'テスト写真集', 'itemPrice' => 3000],
        ]);

        $response = $this->get('/search?keyword=' . urlencode('テスト') . '&category=photobook');

        $response->assertStatus(200);
        $response->assertSee('テスト写真集');
        $response->assertSee('写真集検索結果');
    }

    public function test_dvd_category_uses_jan_as_identifier(): void
    {
        $this->fakeBooksAndIchiba([
            ['jan' => '4988111111111', 'title' => 'テストDVD', 'itemPrice' => 4000],
        ]);

        $response = $this->get('/search?keyword=' . urlencode('テスト') . '&category=dvd');

        $response->assertStatus(200);
        $response->assertSee('テストDVD');
    }

    public function test_tag_filter_narrows_results(): void
    {
        $this->fakeBooksAndIchiba([
            ['isbn' => '1', 'title' => 'アニメ化決定の話題作', 'itemCaption' => ''],
            ['isbn' => '2', 'title' => 'ふつうの作品', 'itemCaption' => ''],
        ]);

        $response = $this->get('/search?keyword=' . urlencode('テスト') . '&tag=' . urlencode('アニメ化'));

        $response->assertStatus(200);
        $response->assertSee('アニメ化決定の話題作');
        $response->assertDontSee('ふつうの作品');
    }

    public function test_invalid_category_falls_back_to_manga(): void
    {
        $this->fakeBooksAndIchiba([
            ['isbn' => '1', 'title' => 'フォールバック作品'],
        ]);

        $response = $this->get('/search?keyword=' . urlencode('テスト') . '&category=invalid');

        $response->assertStatus(200);
        $response->assertSee('マンガ検索結果');
    }
}
