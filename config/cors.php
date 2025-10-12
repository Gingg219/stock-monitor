<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CORS Configuration
    |--------------------------------------------------------------------------
    |
    | CORS determines what cross-origin operations may execute in web browsers.
    | You are free to adjust these settings according to your application needs.
    |
    */

'paths' => ['api/*','login','logout','sanctum/csrf-cookie'],
'allowed_origins' => [
    'http://localhost:5173',
    'http://127.0.0.1:5173',
],
    'allowed_origins_patterns' => [],

    'allowed_methods' => ['*'],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Quan trọng: để Laravel gửi cookie về SPA
    'supports_credentials' => true,
];
