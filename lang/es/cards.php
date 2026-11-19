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
        'color' => 'Color',
        'color_none' => 'Sin color',
        'content' => 'Contenido',
        'grid_w' => 'Ancho (1 a 12)',
        'grid_h' => 'Alto (filas)',
        'is_active' => 'Activa',
    ],

    'attributes' => [
        'board_id' => 'tablero',
        'title' => 'título',
        'icon' => 'icono',
        'color' => 'color',
        'content' => 'contenido',
        'type' => 'tipo',
        'grid_w' => 'ancho',
        'grid_h' => 'alto',
        'is_active' => 'activa',
    ],

    'types' => [
        'links' => 'Enlaces',
        'text' => 'Texto',
    ],

    'new_text' => 'Nueva tarjeta de texto',

    'editor' => [
        'font_size' => 'Tamaño de letra',
        'bold' => 'Negrita',
        'italic' => 'Cursiva',
        'underline' => 'Subrayado',
        'strike' => 'Tachado',
        'code' => 'Código en línea',
        'code_block' => 'Bloque de código',
        'h2' => 'Título',
        'h3' => 'Subtítulo',
        'bullet_list' => 'Lista con viñetas',
        'ordered_list' => 'Lista numerada',
        'quote' => 'Cita',
        'rule' => 'Línea separadora',
        'align_left' => 'Alinear a la izquierda',
        'align_center' => 'Centrar',
        'align_right' => 'Alinear a la derecha',
        'link' => 'Enlace',
        'image' => 'Insertar imagen',
        'image_left' => 'Imagen a la izquierda (el texto la rodea)',
        'image_center' => 'Imagen centrada',
        'image_right' => 'Imagen a la derecha (el texto la rodea)',
        'undo' => 'Deshacer',
        'redo' => 'Rehacer',
        'link_prompt' => 'Dirección del enlace (vacío para quitarlo)',
        'upload_error' => 'No se pudo subir la imagen (JPG, PNG, GIF o WebP, máx. 4 MB).',
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
