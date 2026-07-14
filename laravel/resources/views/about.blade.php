@extends('layouts.app')

@section('title', 'このサイトについて | マンガ口コミ検索')
@section('description', 'マンガ口コミ検索の運営方針、データの出典、口コミの取り扱いについて説明しています。')

@section('content')
<div class="container">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('manga.index') }}">マンガ口コミ検索</a></li>
      <li class="breadcrumb-item active" aria-current="page">このサイトについて</li>
    </ol>
  </nav>

  <h1>このサイトについて</h1>

  <section class="mb-4">
    <h2 class="h5">サイトの目的</h2>
    <p>
      「マンガ口コミ検索」は、マンガ・写真集・DVD/Blu-rayをキーワードやジャンルから検索できるサイトです。
      作品の情報だけでなく、実際に読んだ・観た方の口コミもあわせて確認できるようにしています。
      当サイトは著作権者・販売元の許諾を得た正規の購入・視聴導線（楽天ブックス、電子書籍配信サービス、動画配信サービス等）のみを掲載しており、
      無断で複製・公開された著作物は一切扱っていません。
    </p>
  </section>

  <section class="mb-4">
    <h2 class="h5">掲載データの出典</h2>
    <p>
      掲載している作品情報（タイトル・著者・出版社・画像URL・価格・購入リンク・試し読みリンク等）は、楽天ブックスが提供する
      <a href="https://webservice.rakuten.co.jp/" target="_blank" rel="noopener noreferrer">楽天ウェブサービス</a>
      のAPIを通じて取得しており、随時最新の情報に更新されます。中古商品は楽天市場に出品されている商品情報を表示しています。
      購入・試し読みは楽天ブックス・楽天市場のサイトで行われます。
    </p>
    <p>
      電子書籍配信サービス・動画配信サービスへのリンクは、各サービスのアフィリエイトプログラムを通じて提供しています。
    </p>
  </section>

  <section class="mb-4">
    <h2 class="h5">口コミについて</h2>
    <p>
      口コミは、どなたでもログイン不要で投稿できます。投稿内容は運営による事前確認を行わず即時公開されますが、
      不適切な投稿を発見された場合は内容を精査のうえ対応します。口コミはあくまで投稿者個人の感想であり、
      当サイトが内容の正確性を保証するものではありません。
    </p>
  </section>
</div>
@endsection
