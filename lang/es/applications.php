<?php

return [
    'title' => 'Aplicaciones',
    'empty' => 'Aún no hay aplicaciones.',

    'create' => [
        'title' => 'Nueva aplicación',
    ],

    'edit' => [
        'title' => 'Editar aplicación',
    ],

    'fields' => [
        'name' => 'Nombre',
        'base_url' => 'Dirección base',
        'preview_url' => 'Dirección para la vista previa',
        'requires_auth' => 'Requiere inicio de sesión',
        'notes' => 'Notas',
        'is_active' => 'Activa',
    ],

    'attributes' => [
        'name' => 'nombre',
        'base_url' => 'dirección base',
        'preview_url' => 'dirección para la vista previa',
        'requires_auth' => 'requiere inicio de sesión',
        'notes' => 'notas',
        'is_active' => 'activa',
    ],

    'preview_url_help' => 'Opcional. Solo si la miniatura no carga: la dirección del intermediario, en el mismo servidor que este tablero.',

    'placeholders' => [
        'base_url' => 'http://192.168.0.10:8080',
    ],

    'columns' => [
        'links' => 'Enlaces',
        'status' => 'Estado',
    ],

    'badges' => [
        'auth' => 'Con sesión',
    ],

    'delete' => [
        'title' => '¿Eliminar «:name»?',
    ],

    'messages' => [
        'created' => 'Aplicación creada.',
        'updated' => 'Aplicación actualizada.',
        'deleted' => 'Aplicación eliminada.',
        'has_links' => 'No se puede eliminar «:name»: tiene enlaces.',
    ],

    'validation' => [
        'base_url' => 'La :attribute debe empezar con http:// o https://.',
    ],
];
