<?php

return [

    'paths' => ['*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['https://www.sme-facility.com', '/^https:\/\/(.+\.)?sme-facility\.com$/'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    // 'allowed_headers' => [
    //     'Content-Type',
    //     'X-Requested-With',
    //     'X-Inertia',
    //     'X-Inertia-Version',
    //     'Accept',
    //     'Authorization',
    //     'X-CSRF-TOKEN',
    // ],

    'exposed_headers' => [
        'X-Inertia',
        'X-Inertia-Version',
    ],

    'max_age' => 0,

    'supports_credentials' => true,

];
