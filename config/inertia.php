<?php

return [
    'ssr' => [
        'enabled' => false,
        'runtime' => 'node',
        'ensure_runtime_exists' => false,
        'url' => 'http://127.0.0.1:13714',
        'hot_url' => null,
        'ensure_bundle_exists' => true,
        'throw_on_error' => false,
    ],

    'pages' => [
        'ensure_pages_exist' => true,
        'paths' => [
            resource_path('js/Pages'),
        ],
        'extensions' => [
            'jsx',
        ],
    ],

    'testing' => [
        'ensure_pages_exist' => true,
    ],

    'expose_shared_prop_keys' => true,

    'history' => [
        'encrypt' => false,
    ],

    'devtools' => [
        'enabled' => false,
        'except' => ['telescope*', 'horizon*', '_inertia/devtools*'],
        'storage' => [
            'path' => storage_path('inertia-devtools'),
            'ttl' => 24,
            'prune_interval' => 300,
            'limit' => 100,
        ],
        'middleware' => ['web'],
        'gate' => null,
        'redact' => [
            'keys' => [
                'password',
                'password_confirmation',
                'current_password',
                'token',
                '_token',
                'access_token',
                'refresh_token',
                'secret',
                'client_secret',
                'api_key',
            ],
            'headers' => [
                'cookie',
                'set-cookie',
                'authorization',
                'proxy-authorization',
                'x-xsrf-token',
                'x-csrf-token',
            ],
        ],
    ],
];
