<?php

return [
    'array' => ':attributeは配列である必要があります。',
    'boolean' => ':attributeはtrueまたはfalseである必要があります。',
    'current_password' => 'パスワードが正しくありません。',
    'file' => ':attributeはファイルである必要があります。',
    'image' => ':attributeは画像である必要があります。',
    'mimes' => ':attributeは次の形式のファイルである必要があります：:values。',
    'uploaded' => ':attributeをアップロードできませんでした。',
    'same' => ':attributeは:otherと一致する必要があります。',
    'password' => [
        'letters' => ':attributeには英字を1文字以上含める必要があります。',
        'mixed' => ':attributeには大文字と小文字をそれぞれ1文字以上含める必要があります。',
        'numbers' => ':attributeには数字を1文字以上含める必要があります。',
        'symbols' => ':attributeには記号を1文字以上含める必要があります。',
        'uncompromised' => '指定された:attributeはデータ漏えいで確認されています。別のものを選んでください。',
    ],
    'confirmed' => ':attributeの確認が一致しません。',
    'distinct' => ':attributeに重複した値があります。',
    'email' => ':attributeは有効なメールアドレスである必要があります。',
    'enum' => '選択された:attributeは正しくありません。',
    'exists' => '選択された:attributeは正しくありません。',
    'in' => '選択された:attributeは正しくありません。',
    'integer' => ':attributeは整数である必要があります。',
    'max' => [
        'array' => ':attributeは:max個以下である必要があります。',
        'file' => ':attributeは:maxキロバイト以下である必要があります。',
        'numeric' => ':attributeは:max以下である必要があります。',
        'string' => ':attributeは:max文字以下である必要があります。',
    ],
    'min' => [
        'numeric' => ':attributeは:min以上である必要があります。',
        'string' => ':attributeは:min文字以上である必要があります。',
    ],
    'regex' => ':attributeの形式が正しくありません。',
    'required' => ':attributeは必須です。',
    'string' => ':attributeは文字列である必要があります。',
    'unique' => ':attributeはすでに使用されています。',
    'attributes' => [
    ],
];
