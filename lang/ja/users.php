<?php

return [
    'title' => 'ユーザー',
    'empty' => 'まだユーザーがいません。',
    'create' => [
        'title' => '新しいユーザー',
    ],
    'edit' => [
        'title' => 'ユーザーを編集',
    ],
    'fields' => [
        'name' => '名前',
        'email' => 'メール',
        'role' => 'ロール',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード（確認）',
        'is_active' => '有効',
    ],
    'attributes' => [
        'name' => '名前',
        'email' => 'メール',
        'role' => 'ロール',
        'password' => 'パスワード',
        'is_active' => '有効',
    ],
    'columns' => [
        'role' => 'ロール',
        'status' => '状態',
    ],
    'hints' => [
        'password' => '現在のパスワードを変更しない場合は空のままにしてください。',
    ],
    'delete' => [
        'title' => '「:name」を削除しますか？',
    ],
    'messages' => [
        'created' => 'ユーザーを作成しました。',
        'updated' => 'ユーザーを更新しました。',
        'deleted' => 'ユーザーを削除しました。',
        'self' => '自分のロールを変更したり、アカウントを無効にしたりすることはできません。',
        'self_delete' => '自分のアカウントを削除することはできません。',
        'has_records' => '「:name」は削除できません。システムに変更履歴が記録されています。代わりに無効にしてください。',
    ],
];
