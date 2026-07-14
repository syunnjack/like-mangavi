@extends('layouts.app')

@section('title', 'マンガ口コミ検索 | 試し読み・購入リンクとリアルな口コミで探す')
@section('description', 'マンガ・コミックをキーワードやジャンルから検索できるサイトです。楽天ブックスの試し読み・購入リンクに加えて、実際に読んだ人の口コミも確認できます。')

@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
    '@@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => 'マンガ口コミ検索',
    'url' => url('/'),
    'description' => 'マンガ・コミックをキーワードやジャンルから検索できる情報サイト。',
    'inLanguage' => 'ja',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<div class="container">
  <h1>マンガを探す</h1>
  <p class="text-muted">
    マンガ口コミ検索では、キーワードやジャンルからマンガ・コミックを検索できます。
    楽天ブックスの試し読み・購入リンクに加えて、実際に読んだ人の口コミも確認できます。
  </p>

  <form method="GET" action="{{ route('manga.search') }}" class="row g-2 mb-4">
    <div class="col-7 col-md-8">
      <input type="text" name="keyword" class="form-control" placeholder="マンガ名・作者名で検索" required>
    </div>
    <div class="col-3 col-md-2">
      <select name="category" class="form-select">
        <option value="manga">マンガ</option>
        <option value="photobook">写真集</option>
        <option value="dvd">DVD・Blu-ray</option>
      </select>
    </div>
    <div class="col-2 col-md-2">
      <button type="submit" class="btn btn-primary w-100">検索</button>
    </div>
  </form>

  <h2 class="h5">人気ジャンルから探す</h2>
  <div class="row row-cols-2 row-cols-md-4 g-2 mt-1">
    @foreach ($genres as $genre)
      <div class="col">
        <a href="{{ route('manga.search', ['keyword' => $genre]) }}" class="btn btn-outline-primary w-100">
          {{ $genre }}
        </a>
      </div>
    @endforeach
  </div>

  <section class="mt-5 pt-4 border-top">
    <h2 class="h5">このサイトの特徴</h2>
    <p class="text-muted small">
      各作品ページでは、楽天ブックスの試し読み・購入リンクだけでなく、読んだ人のリアルな口コミも確認できます。
      マンガに加えて写真集・DVD/Blu-rayの検索、中古品や動画配信サービスの一覧表示にも対応しています。
      詳しくは<a href="{{ route('about') }}">このサイトについて</a>をご覧ください。
    </p>
  </section>
</div>
@endsection
