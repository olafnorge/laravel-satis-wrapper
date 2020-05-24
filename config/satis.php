<?php
return [
    'auth_json' => json_decode(docker_secret(env('SATIS_AUTH_JSON', '{}')), true),
    'github_domains' => explode(',', env('SATIS_GITHUB_DOMAINS', '')),
    'gitlab_domains' => explode(',', env('SATIS_GITLAB_DOMAINS', '')),
    'output_dir' => storage_path('satis'),
    'htpasswd_password' => docker_secret(env('SATIS_HTPASSWD_PASSWORD', str_random(32))),
];
