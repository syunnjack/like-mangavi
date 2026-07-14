<?php

namespace App\Support;

class MangaTagger
{
    private const KEYWORD_TAGS = [
        'アニメ化' => 'アニメ化',
        '映画化' => '映画化',
        'ドラマ化' => '実写ドラマ化',
        '完結' => '完結済み',
        '最終巻' => '完結済み',
        '重版' => '重版出来',
        '受賞' => '受賞作',
    ];

    /**
     * @return list<string>
     */
    public static function extract(string $title, string $itemCaption): array
    {
        $text = $title . ' ' . $itemCaption;

        $tags = [];
        foreach (self::KEYWORD_TAGS as $keyword => $label) {
            if (mb_stripos($text, $keyword) !== false) {
                $tags[] = $label;
            }
        }

        return array_values(array_unique($tags));
    }
}
