<?php

namespace App\Http\Controllers;

class SitemapController extends Controller
{
    private const GENRES = [
        '異世界', '恋愛', 'バトル', 'ホラー', 'スポーツ', 'グルメ',
        'ミステリー', '学園', 'ファンタジー', '日常', 'BL', 'TL', '4コマ',
    ];

    public function index()
    {
        $urls = collect([
            ['loc' => route('manga.index'), 'priority' => '1.0'],
            ['loc' => route('about'), 'priority' => '0.3'],
        ])->merge(
            collect(self::GENRES)->map(fn ($genre) => [
                'loc' => route('manga.search', ['keyword' => $genre]),
                'priority' => '0.8',
            ])
        );

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
