<?php

return [
    'array' => 'The :attribute field must be an array.',
    'boolean' => 'The :attribute field must be true or false.',
    'current_password' => 'The password is incorrect.',
    'file' => 'The :attribute field must be a file.',
    'image' => 'The :attribute field must be an image.',
    'mimes' => 'The :attribute field must be a file of type: :values.',
    'uploaded' => 'The :attribute failed to upload.',
    'same' => 'The :attribute field must match :other.',
    'password' => [
        'letters' => 'The :attribute field must contain at least one letter.',
        'mixed' => 'The :attribute field must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The :attribute field must contain at least one number.',
        'symbols' => 'The :attribute field must contain at least one symbol.',
        'uncompromised' => 'The given :attribute has appeared in a data leak. Please choose a different one.',
    ],
    'confirmed' => 'The :attribute confirmation does not match.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'email' => 'The :attribute field must be a valid email address.',
    'enum' => 'The selected :attribute is invalid.',
    'exists' => 'The selected :attribute is invalid.',
    'in' => 'The selected :attribute is invalid.',
    'integer' => 'The :attribute field must be an integer.',
    'max' => [
        'array' => 'The :attribute field must not have more than :max items.',
        'file' => 'The :attribute field must not be greater than :max kilobytes.',
        'numeric' => 'The :attribute field must not be greater than :max.',
        'string' => 'The :attribute field must not be greater than :max characters.',
    ],
    'min' => [
        'numeric' => 'The :attribute field must be at least :min.',
        'string' => 'The :attribute field must be at least :min characters.',
    ],
    'regex' => 'The :attribute format is invalid.',
    'required' => 'The :attribute field is required.',
    'string' => 'The :attribute field must be a string.',
    'unique' => 'The :attribute has already been taken.',
    'attributes' => [
    ],
];
