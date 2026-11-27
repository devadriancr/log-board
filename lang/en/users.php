<?php

return [
    'title' => 'Users',
    'empty' => 'There are no users yet.',
    'create' => [
        'title' => 'New user',
    ],
    'edit' => [
        'title' => 'Edit user',
    ],
    'fields' => [
        'name' => 'Name',
        'email' => 'Email',
        'role' => 'Role',
        'password' => 'Password',
        'password_confirmation' => 'Confirm password',
        'is_active' => 'Active',
    ],
    'attributes' => [
        'name' => 'name',
        'email' => 'email',
        'role' => 'role',
        'password' => 'password',
        'is_active' => 'active',
    ],
    'columns' => [
        'role' => 'Role',
        'status' => 'Status',
    ],
    'hints' => [
        'password' => 'Leave it empty to keep the current one.',
    ],
    'delete' => [
        'title' => 'Delete “:name”?',
    ],
    'messages' => [
        'created' => 'User created.',
        'updated' => 'User updated.',
        'deleted' => 'User deleted.',
        'self' => 'You cannot change your own role or deactivate your account.',
        'self_delete' => 'You cannot delete your own account.',
        'has_records' => '“:name” cannot be deleted: they have changes recorded in the system. Deactivate them instead.',
    ],
];
