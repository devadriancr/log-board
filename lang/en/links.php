<?php

return [
    'title' => 'Links',
    'empty' => 'There are no links yet.',
    'create' => [
        'title' => 'New link',
    ],
    'edit' => [
        'title' => 'Edit link',
    ],
    'fields' => [
        'card_id' => 'Card',
        'application_id' => 'Application',
        'label' => 'Text',
        'label_en' => 'Text (English)',
        'label_ja' => 'Text (Japanese)',
        'path' => 'Path',
        'description' => 'Description',
        'description_en' => 'Description (English)',
        'description_ja' => 'Description (Japanese)',
        'show_description' => 'Show description',
        'open_in_new_tab' => 'Open in a new tab',
        'show_preview' => 'Show preview',
        'preview_size' => 'Preview size',
        'sort_order' => 'Order',
        'is_active' => 'Active',
        'options' => 'Options',
    ],
    'attributes' => [
        'card_id' => 'card',
        'application_id' => 'application',
        'label' => 'text',
        'label_en' => 'English text',
        'label_ja' => 'Japanese text',
        'path' => 'path',
        'description' => 'description',
        'description_en' => 'English description',
        'description_ja' => 'Japanese description',
        'show_description' => 'show description',
        'open_in_new_tab' => 'open in a new tab',
        'show_preview' => 'show preview',
        'preview_size' => 'preview size',
        'sort_order' => 'order',
        'is_active' => 'active',
        'options' => 'options',
        'options.*.value' => 'value',
        'options.*.label' => 'label',
        'options.*.label_en' => 'English label',
        'options.*.label_ja' => 'Japanese label',
    ],
    'preview_help' => 'The page is shown as a thumbnail inside the card.',

    'preview_sizes' => [
        'fill' => 'Fit the card',
        'sm' => 'Small',
        'md' => 'Medium',
        'lg' => 'Large',
    ],

    'placeholders' => [
        'path' => '/report/{param}',
    ],
    'options' => [
        'title' => 'Options for {param}',
        'value' => 'Value',
        'label' => 'Label',
        'add' => 'Add option',
    ],
    'columns' => [
        'card' => 'Card',
        'application' => 'Application',
        'options' => 'Options',
        'status' => 'Status',
    ],
    'delete' => [
        'title' => 'Delete “:name”?',
    ],
    'messages' => [
        'created' => 'Link created.',
        'updated' => 'Link updated.',
        'deleted' => 'Link deleted.',
    ],
];
