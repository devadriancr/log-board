<?php

return [
    'title' => 'Tableros',
    'empty' => 'Aún no hay tableros.',

    'create' => [
        'title' => 'Nuevo tablero',
    ],

    'edit' => [
        'title' => 'Editar tablero',
    ],

    // Etiquetas de los campos (formularios y tabla)
    'fields' => [
        'name' => 'Nombre',
        'slug' => 'Dirección',
        'link_style' => 'Estilo de enlaces',
        'sort_order' => 'Orden',
        'is_home' => 'Tablero de inicio',
        'is_active' => 'Activo',
    ],

    // Nombres de los campos dentro de los mensajes de error (en minúsculas)
    'attributes' => [
        'name' => 'nombre',
        'slug' => 'dirección',
        'link_style' => 'estilo de enlaces',
        'sort_order' => 'orden',
        'is_home' => 'tablero de inicio',
        'is_active' => 'activo',
    ],

    'placeholders' => [
        'slug' => 'principal',
    ],

    'columns' => [
        'cards' => 'Tarjetas',
        'status' => 'Estado',
    ],

    'badges' => [
        'home' => 'Inicio',
    ],

    'delete' => [
        'title' => '¿Eliminar «:name»?',
    ],

    'messages' => [
        'created' => 'Tablero creado.',
        'updated' => 'Tablero actualizado.',
        'deleted' => 'Tablero eliminado.',
        'has_cards' => 'No se puede eliminar «:name»: tiene tarjetas.',
    ],

    'validation' => [
        'slug' => 'La :attribute solo puede tener minúsculas, números y guiones.',
    ],
];
