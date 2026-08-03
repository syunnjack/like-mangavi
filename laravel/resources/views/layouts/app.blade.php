<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'マンガ口コミ検索 | 試し読み・購入リンクとリアルな口コミで探す')</title>
    <meta name="description" content="@yield('description', 'マンガ・コミックをキーワードやジャンルから検索できるサイトです。楽天ブックスの試し読み・購入リンクに加えて、実際に読んだ人の口コミも確認できます。')">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:site_name" content="マンガ口コミ検索">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'マンガ口コミ検索 | 試し読み・購入リンクとリアルな口コミで探す')">
    <meta property="og:description" content="@yield('description', 'マンガ・コミックをキーワードやジャンルから検索できるサイトです。楽天ブックスの試し読み・購入リンクに加えて、実際に読んだ人の口コミも確認できます。')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="ja_JP">

    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="@yield('title', 'マンガ口コミ検索 | 試し読み・購入リンクとリアルな口コミで探す')">
    <meta name="twitter:description" content="@yield('description', 'マンガ・コミックをキーワードやジャンルから検索できるサイトです。楽天ブックスの試し読み・購入リンクに加えて、実際に読んだ人の口コミも確認できます。')">

    <link rel="icon" href="/favicon.ico" sizes="any">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @stack('structured-data')
  @if(config('services.ga4.id'))
  <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.ga4.id') }}"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ config('services.ga4.id') }}');
  </script>
  @endif
</head>
<body>
    <nav class="navbar navbar-dark bg-dark text-white p-3 mb-4">
        <div class="container">
            <a href="{{ route('manga.index') }}" class="h4 mb-0 text-white text-decoration-none">マンガ口コミ検索</a>
        </div>
    </nav>

    <main class="container">
        @yield('content')
    </main>

    <footer class="container text-center text-muted small py-4 mt-4 border-top">
        <a href="{{ route('about') }}" class="text-muted">このサイトについて</a>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
