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
        'path' => 'Ruta',
        'description' => 'Descripción',
        'show_description' => 'Mostrar descripción',
        'open_in_new_tab' => 'Abrir en pestaña nueva',
        'sort_order' => 'Orden',
        'is_active' => 'Activo',
    ],

    'attributes' => [
        'card_id' => 'tarjeta',
        'application_id' => 'aplicación',
        'label' => 'texto',
        'path' => 'ruta',
        'description' => 'descripción',
        'show_description' => 'mostrar descripción',
        'open_in_new_tab' => 'abrir en pestaña nueva',
        'sort_order' => 'orden',
        'is_active' => 'activo',
        'options' => 'opciones',
        'options.*.value' => 'valor',
        'options.*.label' => 'etiqueta',
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
