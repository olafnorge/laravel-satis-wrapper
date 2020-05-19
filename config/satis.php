<?php
return [
    'bitbucket_oauth' => json_decode(docker_secret(env('SATIS_BITBUCKET_OAUTH', '{}')), true),
    'github_domains' => json_decode(env('SATIS_GITHUB_DOMAINS', '[]'), true),
    'github_oauth' => json_decode(docker_secret(env('SATIS_GITHUB_OAUTH', '{}')), true),
    'gitlab_domains' => json_decode(env('SATIS_GITLAB_DOMAINS', '[]'), true),
    'gitlab_oauth' => json_decode(docker_secret(env('SATIS_GITLAB_OAUTH', '{}')), true),
    'gitlab_token' => json_decode(docker_secret(env('SATIS_GITLAB_TOKEN', '{}')), true),
    'http_basic' => json_decode(docker_secret(env('SATIS_HTTP_BASIC', '{}')), true),
    'output_dir' => storage_path('satis'),
    'htpasswd_password' => docker_secret(env('SATIS_HTPASSWD_PASSWORD', str_random(32))),
];
