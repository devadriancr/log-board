<?php

return [
    'title' => 'Usuarios',
    'empty' => 'Aún no hay usuarios.',

    'create' => [
        'title' => 'Nuevo usuario',
    ],

    'edit' => [
        'title' => 'Editar usuario',
    ],

    'fields' => [
        'name' => 'Nombre',
        'email' => 'Correo',
        'role' => 'Rol',
        'password' => 'Contraseña',
        'password_confirmation' => 'Confirmar contraseña',
        'is_active' => 'Activo',
    ],

    'attributes' => [
        'name' => 'nombre',
        'email' => 'correo',
        'role' => 'rol',
        'password' => 'contraseña',
        'is_active' => 'activo',
    ],

    'columns' => [
        'role' => 'Rol',
        'status' => 'Estado',
    ],

    'hints' => [
        'password' => 'Déjala vacía para conservar la actual.',
    ],

    'delete' => [
        'title' => '¿Eliminar a «:name»?',
    ],

    'messages' => [
        'created' => 'Usuario creado.',
        'updated' => 'Usuario actualizado.',
        'deleted' => 'Usuario eliminado.',
        'self' => 'No puedes cambiar tu propio rol ni desactivar tu cuenta.',
        'self_delete' => 'No puedes eliminar tu propia cuenta.',
        'has_records' => 'No se puede eliminar a «:name»: tiene cambios registrados en el sistema. Desactívalo en su lugar.',
    ],
];
