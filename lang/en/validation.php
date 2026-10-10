<?php

return [
    'required' => 'The :attribute field is required.',
    'string' => 'The :attribute must be a string.',
    'max' => [
        'string' => 'The :attribute must not be greater than :max characters.',
        'file' => 'The :attribute must not be greater than :max kilobytes.',
    ],
    'min' => [
        'string' => 'The :attribute must be at least :min characters.',
    ],
    'email' => 'The :attribute must be a valid email address.',
    'unique' => 'The :attribute has already been taken.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'in' => 'The selected :attribute is invalid.',
    'image' => 'The :attribute must be an image.',
    'mimes' => 'The :attribute must be a file of type: :values.',
];
