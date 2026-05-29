<?php

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => (static function () {
        $origins = env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000,http://127.0.0.1:3000');
        if ($origins === '*') {
            return ['*'];
        }

        return array_values(array_filter(array_map('trim', explode(',', $origins))));
    })(),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // JWT Bearer 认证，不使用 Cookie Session
    'supports_credentials' => false,

];
