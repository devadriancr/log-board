<?php

return [
    'title' => 'アプリケーション',
    'empty' => 'まだアプリケーションがありません。',
    'create' => [
        'title' => '新しいアプリケーション',
    ],
    'edit' => [
        'title' => 'アプリケーションを編集',
    ],
    'fields' => [
        'name' => '名前',
        'base_url' => 'ベースアドレス',
        'requires_auth' => 'ログインが必要',
        'notes' => 'メモ',
        'is_active' => '有効',
    ],
    'attributes' => [
        'name' => '名前',
        'base_url' => 'ベースアドレス',
        'requires_auth' => 'ログインが必要',
        'notes' => 'メモ',
        'is_active' => '有効',
    ],
    'placeholders' => [
        'base_url' => 'http://192.168.0.10:8080',
    ],
    'columns' => [
        'links' => 'リンク',
        'status' => '状態',
    ],
    'badges' => [
        'auth' => 'ログイン必須',
    ],
    'delete' => [
        'title' => '「:name」を削除しますか？',
    ],
    'messages' => [
        'created' => 'アプリケーションを作成しました。',
        'updated' => 'アプリケーションを更新しました。',
        'deleted' => 'アプリケーションを削除しました。',
        'has_links' => '「:name」は削除できません。リンクがあります。',
    ],
    'validation' => [
        'base_url' => ':attributeは http:// または https:// で始まる必要があります。',
    ],
];
