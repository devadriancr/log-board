<?php

return [
    'title' => 'Boards',
    'empty' => 'There are no boards yet.',
    'actions' => [
        'design' => 'Design',
    ],
    'create' => [
        'title' => 'New board',
    ],
    'edit' => [
        'title' => 'Edit board',
    ],
    'fields' => [
        'name' => 'Name',
        'name_en' => 'Name (English)',
        'name_ja' => 'Name (Japanese)',
        'slug' => 'Address',
        'link_style' => 'Link style',
        'sort_order' => 'Order',
        'is_home' => 'Home board',
        'is_active' => 'Active',
    ],
    'attributes' => [
        'name' => 'name',
        'name_en' => 'English name',
        'name_ja' => 'Japanese name',
        'slug' => 'address',
        'link_style' => 'link style',
        'sort_order' => 'order',
        'is_home' => 'home board',
        'is_active' => 'active',
    ],
    'placeholders' => [
        'slug' => 'main',
    ],
    'columns' => [
        'cards' => 'Cards',
        'status' => 'Status',
    ],
    'badges' => [
        'home' => 'Home',
    ],
    'delete' => [
        'title' => 'Delete “:name”?',
    ],
    'messages' => [
        'created' => 'Board created.',
        'updated' => 'Board updated.',
        'deleted' => 'Board deleted.',
        'has_cards' => '“:name” cannot be deleted: it has cards.',
    ],
    'validation' => [
        'slug' => 'The :attribute may only contain lowercase letters, numbers and hyphens.',
    ],
];
