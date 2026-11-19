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
        'icon' => 'Icono',
        'icon_none' => 'Sin icono',
        'icon_search' => 'Buscar icono…',
        'grid_w' => 'Ancho (1 a 12)',
        'grid_h' => 'Alto (filas)',
        'is_active' => 'Activa',
    ],

    'attributes' => [
        'board_id' => 'tablero',
        'title' => 'título',
        'icon' => 'icono',
        'grid_w' => 'ancho',
        'grid_h' => 'alto',
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
