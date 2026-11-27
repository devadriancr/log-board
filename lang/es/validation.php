<?php

// Solo incluye las reglas que se usan hasta ahora. Las que falten se muestran
// con el texto en inglés del framework (idioma de respaldo).

return [
    'array' => 'El campo :attribute debe ser una lista.',
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'current_password' => 'La contraseña es incorrecta.',
    'file' => 'El campo :attribute debe ser un archivo.',
    'image' => 'El campo :attribute debe ser una imagen.',
    'mimes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
    'uploaded' => 'No se pudo subir el archivo de :attribute.',
    'same' => 'El campo :attribute debe coincidir con :other.',
    'password' => [
        'letters' => 'El campo :attribute debe tener al menos una letra.',
        'mixed' => 'El campo :attribute debe tener al menos una mayúscula y una minúscula.',
        'numbers' => 'El campo :attribute debe tener al menos un número.',
        'symbols' => 'El campo :attribute debe tener al menos un símbolo.',
        'uncompromised' => 'La contraseña de :attribute apareció en una filtración de datos. Elige otra.',
    ],
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'distinct' => 'El campo :attribute tiene un valor repetido.',
    'email' => 'El campo :attribute debe ser un correo válido.',
    'enum' => 'El valor seleccionado en :attribute no es válido.',
    'exists' => 'El valor seleccionado en :attribute no es válido.',
    'in' => 'El valor seleccionado en :attribute no es válido.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'max' => [
        'array' => 'El campo :attribute no puede tener más de :max elementos.',
        'file' => 'El campo :attribute no puede pesar más de :max kilobytes.',
        'numeric' => 'El campo :attribute no puede ser mayor que :max.',
        'string' => 'El campo :attribute no puede tener más de :max caracteres.',
    ],
    'min' => [
        'numeric' => 'El campo :attribute debe ser como mínimo :min.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'regex' => 'El formato de :attribute no es válido.',
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser un texto.',
    'unique' => 'El valor de :attribute ya está en uso.',

    'attributes' => [],
];
