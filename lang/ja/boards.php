<?php

return [
    'title' => 'ボード',
    'empty' => 'まだボードがありません。',
    'actions' => [
        'design' => 'デザイン',
    ],
    'create' => [
        'title' => '新しいボード',
    ],
    'edit' => [
        'title' => 'ボードを編集',
    ],
    'fields' => [
        'name' => '名前',
        'name_en' => '名前（英語）',
        'name_ja' => '名前（日本語）',
        'slug' => 'アドレス',
        'link_style' => 'リンクのスタイル',
        'sort_order' => '順序',
        'is_home' => 'ホームボード',
        'is_active' => '有効',
    ],
    'attributes' => [
        'name' => '名前',
        'name_en' => '英語の名前',
        'name_ja' => '日本語の名前',
        'slug' => 'アドレス',
        'link_style' => 'リンクのスタイル',
        'sort_order' => '順序',
        'is_home' => 'ホームボード',
        'is_active' => '有効',
    ],
    'placeholders' => [
        'slug' => 'main',
    ],
    'columns' => [
        'cards' => 'カード',
        'status' => '状態',
    ],
    'badges' => [
        'home' => 'ホーム',
    ],
    'delete' => [
        'title' => '「:name」を削除しますか？',
    ],
    'messages' => [
        'created' => 'ボードを作成しました。',
        'updated' => 'ボードを更新しました。',
        'deleted' => 'ボードを削除しました。',
        'has_cards' => '「:name」は削除できません。カードがあります。',
    ],
    'validation' => [
        'slug' => ':attributeには小文字の英字、数字、ハイフンのみ使用できます。',
    ],
];
