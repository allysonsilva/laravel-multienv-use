<?php

return [
    'name' => env('ENV_NAME'),

    'names' => [
        'default' => env('DEFAULT_ENV_NAME'),
        'envA' => env('ENV_NAME_A'),
        'envB' => env('ENV_NAME_B'),
        'envC' => env('ENV_NAME_C'),
    ],

    'app' => [
        'name' => env('APP_NAME'),
        'debug' => env('APP_DEBUG'),
        'url' => env('APP_URL'),
    ],
];
