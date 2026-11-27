<?php

return [
    'title' => 'Applications',
    'empty' => 'There are no applications yet.',
    'create' => [
        'title' => 'New application',
    ],
    'edit' => [
        'title' => 'Edit application',
    ],
    'fields' => [
        'name' => 'Name',
        'base_url' => 'Base address',
        'preview_url' => 'Preview address',
        'requires_auth' => 'Requires login',
        'notes' => 'Notes',
        'is_active' => 'Active',
    ],
    'attributes' => [
        'name' => 'name',
        'base_url' => 'base address',
        'preview_url' => 'preview address',
        'requires_auth' => 'requires login',
        'notes' => 'notes',
        'is_active' => 'active',
    ],
    'preview_url_help' => 'Optional. Only if the thumbnail does not load: the address of the relay, on the same server as this board.',

    'placeholders' => [
        'base_url' => 'http://192.168.0.10:8080',
    ],
    'columns' => [
        'links' => 'Links',
        'status' => 'Status',
    ],
    'badges' => [
        'auth' => 'Login required',
    ],
    'delete' => [
        'title' => 'Delete “:name”?',
    ],
    'messages' => [
        'created' => 'Application created.',
        'updated' => 'Application updated.',
        'deleted' => 'Application deleted.',
        'has_links' => '“:name” cannot be deleted: it has links.',
    ],
    'validation' => [
        'base_url' => 'The :attribute must start with http:// or https://.',
    ],
];
