@extends('layouts.app')

@section('title', ($tag ? $tag . '｜' : '') . $keyword . 'の検索結果 | マンガ口コミ検索')
@section('description', $keyword . 'に関連する' . $categoryLabel . 'の一覧です。試し読み・購入リンク・実際に読んだ人の口コミをまとめて確認できます。')

@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'マンガ口コミ検索', 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => $keyword . 'の検索結果'],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@if (!empty($faq))
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => collect($faq)->map(fn ($qa) => [
        '@type' => 'Question',
        'name' => $qa['question'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => $qa['answer'],
        ],
    ])->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
@if (!empty($results))
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => $keyword . 'の' . $categoryLabel . '検索結果',
    'itemListElement' => collect($results)->values()->map(function ($item, $i) use ($reviews, $idField) {
        $itemReviews = $reviews->get($item[$idField] ?? null);

        $entry = [
            '@type' => 'Product',
            'name' => $item['title'] ?? '',
            'url' => $item['itemUrl'] ?? null,
            'image' => $item['largeImageUrl'] ?? null,
        ];

        if ($itemReviews && $itemReviews->count() > 0) {
            $entry['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => round($itemReviews->avg('rating'), 1),
                'reviewCount' => $itemReviews->count(),
            ];
        }

        return [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'item' => $entry,
        ];
    })->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
@endpush

@section('content')
<div class="container">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('manga.index') }}">マンガ口コミ検索</a></li>
      <li class="breadcrumb-item active" aria-current="page">{{ $keyword }}{{ $tag ? '（' . $tag . '）' : '' }}</li>
    </ol>
  </nav>

  <h1>「{{ $keyword }}」の{{ $categoryLabel }}検索結果</h1>

  @if($category === 'manga' && !empty($externalServices))
    <div class="alert alert-light border mb-3">
      <span class="small text-muted">電子書籍で読むなら:</span>
      @foreach($externalServices as $service)
        <a href="{{ $service['url'] }}" class="btn btn-sm btn-outline-secondary me-1" target="_blank" rel="noopener noreferrer sponsored">{{ $service['name'] }}</a>
      @endforeach
    </div>
  @endif

  @if($category === 'dvd' && !empty($videoServices))
    <div class="alert alert-light border mb-3">
      <span class="small text-muted">動画配信・PPVで観るなら:</span>
      @foreach($videoServices as $service)
        <a href="{{ $service['url'] }}" class="btn btn-sm btn-outline-secondary me-1" target="_blank" rel="noopener noreferrer sponsored">{{ $service['name'] }}</a>
      @endforeach
    </div>
  @endif

  @if(!empty($availableTags))
    <div class="mb-3">
      <span class="small text-muted">絞り込む:</span>
      @foreach($availableTags as $t)
        <a href="{{ route('manga.search', ['keyword' => $keyword, 'tag' => $t, 'category' => $category]) }}"
           class="btn btn-sm {{ $tag === $t ? 'btn-primary' : 'btn-outline-secondary' }} me-1 mb-1">{{ $t }}</a>
      @endforeach
      @if($tag !== '')
        <a href="{{ route('manga.search', ['keyword' => $keyword, 'category' => $category]) }}" class="btn btn-sm btn-link">絞り込み解除</a>
      @endif
    </div>
  @endif

  @if(empty($results))
    <p>「{{ $keyword }}」{{ $tag ? "（{$tag}）" : '' }}に一致する{{ $categoryLabel }}が見つかりませんでした。別のキーワードもお試しください。</p>
    <a href="{{ route('manga.index') }}" class="btn btn-outline-primary">トップに戻る</a>
  @else
    <p class="text-muted">{{ count($results) }}件の作品を掲載しています。</p>
    @foreach($results as $item)
      @php
        $itemId = $item[$idField] ?? null;
        $itemReviews = $reviews->get($itemId, collect());
        $itemTags = $tagsByItemId[$itemId] ?? [];
      @endphp
      <article class="mb-4 pb-4 border-bottom row">
        <div class="col-3 col-md-2">
          @if(!empty($item['largeImageUrl']))
            <img src="{{ $item['largeImageUrl'] }}" alt="{{ $item['title'] ?? '' }}" class="img-fluid" loading="lazy">
          @endif
        </div>
        <div class="col-9 col-md-10">
          <h2 class="h5">{{ $item['title'] ?? '' }}</h2>
          <p class="mb-1 text-muted small">{{ $item['author'] ?? ($item['artistName'] ?? '') }}{{ !empty($item['publisherName']) ? ' / ' . $item['publisherName'] : '' }}</p>
          @if(!empty($item['itemPrice']))
            <p class="mb-1">{{ number_format($item['itemPrice']) }}円（税込）</p>
          @endif
          @if(!empty($item['reviewCount']))
            <p class="mb-1 small text-muted">楽天ブックス評価: ★{{ $item['reviewAverage'] ?? '-' }}（{{ $item['reviewCount'] }}件）</p>
          @endif
          @if(!empty($itemTags))
            <p class="mb-2">
              @foreach($itemTags as $t)
                <span class="badge bg-info text-dark me-1">{{ $t }}</span>
              @endforeach
            </p>
          @endif
          <div class="mb-2">
            @if(!empty($item['chirayomiUrl']))
              <a href="{{ $item['chirayomiUrl'] }}" class="btn btn-sm btn-outline-success me-1" target="_blank" rel="noopener noreferrer">試し読み</a>
            @endif
            <a href="{{ $item['affiliateUrl'] ?? ($item['itemUrl'] ?? '#') }}" class="btn btn-sm btn-primary" target="_blank" rel="noopener noreferrer sponsored">楽天ブックスで見る</a>
          </div>

          @if($itemReviews->isEmpty())
            <p class="text-muted small">まだ口コミがありません。最初の口コミを投稿してみませんか？</p>
          @else
            <p class="fw-bold small mb-2">
              口コミ {{ $itemReviews->count() }}件（平均★{{ round($itemReviews->avg('rating'), 1) }}）
            </p>
            @foreach($itemReviews as $review)
              <div class="border rounded p-2 mb-2 small">
                <div>{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                  <strong>{{ $review->nickname }}</strong>
                  <span class="text-muted">{{ $review->created_at->format('Y-m-d') }}</span>
                </div>
                <div>{{ $review->comment }}</div>
              </div>
            @endforeach
          @endif

          <details class="mt-2">
            <summary class="small">口コミを投稿する</summary>
            <form method="POST" action="{{ route('reviews.store') }}" class="mt-2">
              @csrf
              <input type="hidden" name="item_id" value="{{ $itemId }}">
              <input type="hidden" name="title" value="{{ $item['title'] ?? '' }}">
              <div style="position:absolute;left:-9999px;" aria-hidden="true">
                <label>ウェブサイト <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
              </div>
              <div class="mb-2">
                <label class="form-label small">ニックネーム（任意）</label>
                <input type="text" name="nickname" class="form-control form-control-sm" maxlength="30">
              </div>
              <div class="mb-2">
                <label class="form-label small">評価</label>
                <select name="rating" class="form-select form-select-sm" required>
                  <option value="">選択してください</option>
                  <option value="5">★★★★★</option>
                  <option value="4">★★★★☆</option>
                  <option value="3">★★★☆☆</option>
                  <option value="2">★★☆☆☆</option>
                  <option value="1">★☆☆☆☆</option>
                </select>
              </div>
              <div class="mb-2">
                <label class="form-label small">口コミ</label>
                <textarea name="comment" class="form-control form-control-sm" rows="3" minlength="5" maxlength="1000" required></textarea>
              </div>
              @if ($errors->any())
                <p class="text-danger small">{{ $errors->first() }}</p>
              @endif
              <button type="submit" class="btn btn-sm btn-outline-primary">投稿する</button>
            </form>
          </details>
        </div>
      </article>
    @endforeach

    @if(!empty($usedItems))
      <section class="mt-4 pt-4 border-top">
        <h2 class="h5">中古で探す</h2>
        <p class="text-muted small">楽天市場に出品されている「{{ $keyword }} 中古」の関連商品です。</p>
        <div class="row row-cols-2 row-cols-md-5 g-2">
          @foreach($usedItems as $item)
            <div class="col">
              <a href="{{ $item['itemUrl'] ?? '#' }}" target="_blank" rel="noopener noreferrer sponsored" class="text-decoration-none text-body">
                @if(!empty($item['mediumImageUrls'][0]))
                  <img src="{{ $item['mediumImageUrls'][0] }}" alt="{{ $item['itemName'] ?? '' }}" class="img-fluid mb-1" loading="lazy">
                @endif
                <div class="small">{{ Str::limit($item['itemName'] ?? '', 40) }}</div>
                @if(!empty($item['itemPrice']))
                  <div class="small fw-bold">{{ number_format($item['itemPrice']) }}円</div>
                @endif
              </a>
            </div>
          @endforeach
        </div>
      </section>
    @endif

    @if(!empty($faq))
      <section class="mt-4 pt-4 border-top">
        <h2 class="h5">よくある質問</h2>
        @foreach($faq as $qa)
          <div class="mb-3">
            <p class="fw-bold mb-1">Q. {{ $qa['question'] }}</p>
            <p class="mb-0">A. {{ $qa['answer'] }}</p>
          </div>
        @endforeach
      </section>
    @endif
  @endif
</div>
@endsection
