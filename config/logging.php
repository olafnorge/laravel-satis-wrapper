<?php

use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */
    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => value(function (): array {
                $channels = explode(',', env('LOG_STACK_CHANNELS', 'single'));

                if (in_array('rollbar', $channels) && (bool)env('ROLLBAR_ENABLED', false) === false) {
                    $channels = array_filter($channels, function ($item) {
                        return $item !== 'rollbar';
                    });
                }

                return array_filter($channels) ?: ['single'];
            }),
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'rollbar' => [
            'access_token' => docker_secret(env('ROLLBAR_TOKEN', md5(''))),
            'allow_exec' => false,
            'autodetect_branch' => false,
            'branch' => env('ROLLBAR_BRANCH', 'master'),
            'code_version' => env('ROLLBAR_CODE_VERSION', 'HEAD'),
            'driver' => 'monolog',
            'enabled' => env('ROLLBAR_ENABLED', false),
            'environment' => env('ROLLBAR_ENVIRONMENT', config('app.env')),
            'framework' => 'laravel',
            'handler' => \Rollbar\Laravel\MonologHandler::class,
            'root' => base_path(),
            'level' => env('LOG_LEVEL', 'debug'),
            'host' => env('HOST', 'satis.example.com'),
        ],
    ],

];
