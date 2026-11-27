<?php

return [
    'title' => 'Enlaces',
    'empty' => 'Aún no hay enlaces.',

    'create' => [
        'title' => 'Nuevo enlace',
    ],

    'edit' => [
        'title' => 'Editar enlace',
    ],

    'fields' => [
        'card_id' => 'Tarjeta',
        'application_id' => 'Aplicación',
        'label' => 'Texto',
        'label_en' => 'Texto (inglés)',
        'label_ja' => 'Texto (japonés)',
        'path' => 'Ruta',
        'description' => 'Descripción',
        'description_en' => 'Descripción (inglés)',
        'description_ja' => 'Descripción (japonés)',
        'show_description' => 'Mostrar descripción',
        'open_in_new_tab' => 'Abrir en pestaña nueva',
        'show_preview' => 'Mostrar vista previa',
        'preview_size' => 'Tamaño de la vista previa',
        'sort_order' => 'Orden',
        'is_active' => 'Activo',
        'options' => 'Opciones',
    ],

    'attributes' => [
        'card_id' => 'tarjeta',
        'application_id' => 'aplicación',
        'label' => 'texto',
        'label_en' => 'texto en inglés',
        'label_ja' => 'texto en japonés',
        'path' => 'ruta',
        'description' => 'descripción',
        'description_en' => 'descripción en inglés',
        'description_ja' => 'descripción en japonés',
        'show_description' => 'mostrar descripción',
        'open_in_new_tab' => 'abrir en pestaña nueva',
        'show_preview' => 'mostrar vista previa',
        'preview_size' => 'tamaño de la vista previa',
        'sort_order' => 'orden',
        'is_active' => 'activo',
        'options' => 'opciones',
        'options.*.value' => 'valor',
        'options.*.label' => 'etiqueta',
        'options.*.label_en' => 'etiqueta en inglés',
        'options.*.label_ja' => 'etiqueta en japonés',
    ],

    'preview_help' => 'La página se ve en miniatura dentro de la tarjeta.',

    'preview_sizes' => [
        'fill' => 'Ajustar a la tarjeta',
        'sm' => 'Pequeña',
        'md' => 'Mediana',
        'lg' => 'Grande',
    ],

    'placeholders' => [
        'path' => '/reporte/{param}',
    ],

    'options' => [
        'title' => 'Opciones de {param}',
        'value' => 'Valor',
        'label' => 'Etiqueta',
        'add' => 'Agregar opción',
    ],

    'columns' => [
        'card' => 'Tarjeta',
        'application' => 'Aplicación',
        'options' => 'Opciones',
        'status' => 'Estado',
    ],

    'delete' => [
        'title' => '¿Eliminar «:name»?',
    ],

    'messages' => [
        'created' => 'Enlace creado.',
        'updated' => 'Enlace actualizado.',
        'deleted' => 'Enlace eliminado.',
    ],
];
