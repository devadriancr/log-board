<?php

return [
    'title' => 'Tarjetas',
    'empty' => 'Aún no hay tarjetas.',

    'create' => [
        'title' => 'Nueva tarjeta',
    ],

    'edit' => [
        'title' => 'Editar tarjeta',
    ],

    'fields' => [
        'board_id' => 'Tablero',
        'title' => 'Título',
        'grid_w' => 'Ancho (1 a 12)',
        'grid_h' => 'Alto (filas)',
        'link_columns' => 'Columnas de enlaces',
        'is_active' => 'Activa',
    ],

    'attributes' => [
        'board_id' => 'tablero',
        'title' => 'título',
        'grid_w' => 'ancho',
        'grid_h' => 'alto',
        'link_columns' => 'columnas de enlaces',
        'is_active' => 'activa',
    ],

    'columns' => [
        'size' => 'Tamaño',
        'links' => 'Enlaces',
        'status' => 'Estado',
    ],

    'delete' => [
        'title' => '¿Eliminar «:name»?',
    ],

    'messages' => [
        'created' => 'Tarjeta creada.',
        'updated' => 'Tarjeta actualizada.',
        'deleted' => 'Tarjeta eliminada.',
        'has_links' => 'No se puede eliminar «:name»: tiene enlaces.',
    ],
];
