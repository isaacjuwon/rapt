<?php

return [
    'disk' => 'public',
    'directory' => 'media',
    'max_size_kb' => 8192,
    'allowed_mimes' => [
        'image/jpeg',
        'image/png',
        'image/webp',
    ],
    'conversions' => [
        'thumb' => ['w' => 400, 'h' => 400, 'fit' => 'cover'],
        'preview' => ['w' => 1200, 'h' => 800, 'fit' => 'contain'],
    ],
    'routes_middleware' => ['web', 'auth'],
    'theme' => 'default', // default|flux|bootstrap
];