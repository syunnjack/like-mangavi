<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Support\MangaTagger;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MangaController extends Controller
{
    private const GENRES = [
        '異世界', '恋愛', 'バトル', 'ホラー', 'スポーツ', 'グルメ',
        'ミステリー', '学園', 'ファンタジー', '日常', 'BL', 'TL', '4コマ',
    ];

    private const CATEGORIES = [
        'manga' => [
            'label' => 'マンガ',
            'endpoint' => 'https://openapi.rakuten.co.jp/services/api/BooksBook/Search/20170404',
            'params' => ['size' => 9],
            'idField' => 'isbn',
        ],
        'photobook' => [
            'label' => '写真集',
            'endpoint' => 'https://openapi.rakuten.co.jp/services/api/BooksBook/Search/20170404',
            'params' => ['booksGenreId' => '001013'],
            'idField' => 'isbn',
        ],
        'dvd' => [
            'label' => 'DVD・Blu-ray',
            'endpoint' => 'https://openapi.rakuten.co.jp/services/api/BooksDVD/Search/20170404',
            'params' => [],
            'idField' => 'jan',
        ],
    ];

    public function index()
    {
        return view('manga.index', ['genres' => self::GENRES]);
    }

    public function search(Request $request)
    {
        $keyword = trim($request->input('keyword', ''));
        $category = array_key_exists($request->input('category', 'manga'), self::CATEGORIES)
            ? $request->input('category', 'manga')
            : 'manga';
        $categoryConfig = self::CATEGORIES[$category];
        $idField = $categoryConfig['idField'];

        if ($keyword === '') {
            return redirect()->route('manga.index');
        }

        $results = Cache::remember("manga-search:{$category}:{$keyword}", now()->addHour(), function () use ($keyword, $categoryConfig) {
            try {
                $response = Http::timeout(5)
                    ->withHeaders([
                        'Referer' => config('app.url'),
                        'Origin' => config('app.url'),
                    ])
                    ->get($categoryConfig['endpoint'], array_merge([
                        'format' => 'json',
                        'formatVersion' => 2,
                        'applicationId' => env('RAKUTEN_APP_ID'),
                        'accessKey' => env('RAKUTEN_ACCESS_KEY'),
                        'affiliateId' => env('RAKUTEN_AFFILIATE_ID'),
                        'title' => $keyword,
                        'hits' => 30,
                    ], $categoryConfig['params']));
            } catch (ConnectionException) {
                return [];
            }

            return $response->successful() ? ($response->json('Items') ?? []) : [];
        });

        $tagsByItemId = [];
        $availableTags = [];
        foreach ($results as $item) {
            $tags = MangaTagger::extract($item['title'] ?? '', $item['itemCaption'] ?? '');
            if (isset($item[$idField])) {
                $tagsByItemId[$item[$idField]] = $tags;
            }
            $availableTags = array_unique(array_merge($availableTags, $tags));
        }
        sort($availableTags);

        $tag = $request->input('tag', '');
        if ($tag !== '') {
            $results = array_values(array_filter($results, function ($item) use ($tag, $tagsByItemId, $idField) {
                return in_array($tag, $tagsByItemId[$item[$idField] ?? null] ?? [], true);
            }));
        }

        $itemIds = collect($results)
            ->map(fn ($item) => $item[$idField] ?? null)
            ->filter()
            ->values();

        $reviews = Review::whereIn('item_id', $itemIds)
            ->latest()
            ->get()
            ->groupBy('item_id');

        $externalServices = collect(config('manga_services'))
            ->filter(fn ($service) => filled($service['url'] ?? null))
            ->values();

        $videoServices = collect(config('video_services'))
            ->filter(fn ($service) => filled($service['url'] ?? null))
            ->values();

        $faq = $this->buildFaq($keyword, $reviews, $categoryConfig['label']);
        $categoryLabel = $categoryConfig['label'];
        $usedItems = $this->fetchUsedItems($keyword);

        return view('manga.results', compact(
            'results', 'keyword', 'reviews', 'tagsByItemId', 'availableTags', 'tag', 'idField',
            'externalServices', 'videoServices', 'faq', 'category', 'categoryLabel', 'usedItems'
        ));
    }

    private function fetchUsedItems(string $keyword): array
    {
        return Cache::remember('manga-used:' . $keyword, now()->addHour(), function () use ($keyword) {
            try {
                $response = Http::timeout(5)
                    ->withHeaders([
                        'Referer' => config('app.url'),
                        'Origin' => config('app.url'),
                    ])
                    ->get('https://openapi.rakuten.co.jp/ichibams/api/IchibaItem/Search/20260701', [
                        'format' => 'json',
                        'formatVersion' => 2,
                        'applicationId' => env('RAKUTEN_APP_ID'),
                        'accessKey' => env('RAKUTEN_ACCESS_KEY'),
                        'affiliateId' => env('RAKUTEN_AFFILIATE_ID'),
                        'keyword' => $keyword . ' 中古',
                        'hits' => 5,
                    ]);
            } catch (ConnectionException) {
                return [];
            }

            return $response->successful() ? ($response->json('Items') ?? []) : [];
        });
    }

    private function buildFaq(string $keyword, Collection $reviews, string $categoryLabel): array
    {
        $topRated = $reviews->filter(fn ($group) => $group->count() > 0)
            ->sortByDesc(fn ($group) => $group->avg('rating'))
            ->first();
        $topRatedTitle = $topRated ? $topRated->first()->title : null;

        $faq = [
            [
                'question' => "「{$keyword}」に関連する{$categoryLabel}の試し読みはできますか？",
                'answer' => '楽天ブックスで試し読みが提供されている作品には、各作品ページに「試し読み」リンクを表示しています。試し読みが無い作品は、購入リンクから詳細を確認できます。',
            ],
            [
                'question' => "「{$keyword}」の口コミは見られますか？",
                'answer' => '各作品ページで、楽天ブックスのレビュー件数・評価に加えて、当サイト独自に投稿された口コミ（評価と感想）も確認できます。口コミはどなたでもログイン不要で投稿できます。',
            ],
        ];

        if ($topRatedTitle) {
            $faq[] = [
                'question' => "「{$keyword}」でおすすめの作品は？",
                'answer' => "口コミ評価をもとにすると、「{$topRatedTitle}」が現在最も高い評価を得ています。ただし好みは人それぞれのため、他の作品の口コミもあわせてご確認ください。",
            ];
        }

        return $faq;
    }
}
