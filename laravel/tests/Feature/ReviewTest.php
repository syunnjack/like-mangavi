<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_review_can_be_submitted(): void
    {
        $response = $this->post(route('reviews.store'), [
            'item_id' => '9784088821559',
            'title' => 'テストマンガ',
            'nickname' => 'テスト太郎',
            'rating' => 5,
            'comment' => 'とても面白かったです。',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'item_id' => '9784088821559',
            'nickname' => 'テスト太郎',
            'rating' => 5,
        ]);
    }

    public function test_review_without_nickname_defaults_to_anonymous(): void
    {
        $this->post(route('reviews.store'), [
            'item_id' => '9784088821559',
            'title' => 'テストマンガ',
            'rating' => 4,
            'comment' => '良かったです。',
        ]);

        $this->assertDatabaseHas('reviews', ['nickname' => '匿名']);
    }

    public function test_honeypot_field_silently_rejects_the_review(): void
    {
        $this->post(route('reviews.store'), [
            'item_id' => '9784088821559',
            'title' => 'テストマンガ',
            'rating' => 5,
            'comment' => 'スパムコメントです。',
            'website' => 'https://spam.example.com',
        ]);

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_ng_word_is_rejected(): void
    {
        $response = $this->post(route('reviews.store'), [
            'item_id' => '9784088821559',
            'title' => 'テストマンガ',
            'rating' => 1,
            'comment' => 'この作者は死ねばいいのに',
        ]);

        $response->assertSessionHasErrors('comment');
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_rating_out_of_range_is_rejected(): void
    {
        $response = $this->post(route('reviews.store'), [
            'item_id' => '9784088821559',
            'title' => 'テストマンガ',
            'rating' => 6,
            'comment' => '評価が範囲外です。',
        ]);

        $response->assertSessionHasErrors('rating');
    }
}
