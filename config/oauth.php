<?php
return [
    // generic config, gets inherited from others if not defined independently
    'client_id' => env('OAUTH_CLIENT_ID'),
    'client_secret' => docker_secret(env('OAUTH_CLIENT_SECRET')),
    'redirect' => env('OAUTH_REDIRECT'),

    // github specific settings
    'github' => [
        'client_id' => env('OAUTH_GITHUB_CLIENT_ID', env('OAUTH_CLIENT_ID')),
        'client_secret' => docker_secret(env('OAUTH_GITHUB_CLIENT_SECRET', env('OAUTH_CLIENT_SECRET'))),
        'redirect' => env('OAUTH_GITHUB_REDIRECT', env('OAUTH_REDIRECT')),
    ],
    // google specific settings
    'google' => [
        'client_id' => env('OAUTH_GOOGLE_CLIENT_ID', env('OAUTH_CLIENT_ID')),
        'client_secret' => docker_secret(env('OAUTH_GOOGLE_CLIENT_SECRET', env('OAUTH_CLIENT_SECRET'))),
        'redirect' => env('OAUTH_GOOGLE_REDIRECT', env('OAUTH_REDIRECT')),
    ],
    // linkedin specific settings
    'linkedin' => [
        'client_id' => env('OAUTH_LINKEDIN_CLIENT_ID', env('OAUTH_CLIENT_ID')),
        'client_secret' => docker_secret(env('OAUTH_LINKEDIN_CLIENT_SECRET', env('OAUTH_CLIENT_SECRET'))),
        'redirect' => env('OAUTH_LINKEDIN_REDIRECT', env('OAUTH_REDIRECT')),
    ],
];
