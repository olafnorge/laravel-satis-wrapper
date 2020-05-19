<?php
return [
    'default' => env('LOCK_DRIVER', 'semaphore'),
    'stores' => [
        'flock' => [
            'driver' => \Symfony\Component\Lock\Store\FlockStore::class,
            'lock_path' => storage_path('framework/cache'),
        ],
        'redis' => [
            'driver' => \Symfony\Component\Lock\Store\RedisStore::class,
            'connection' => env('LOCK_REDIS_CONNECTION', 'default'),
        ],
        'semaphore' => [
            'driver' => \Symfony\Component\Lock\Store\SemaphoreStore::class,
        ],
    ],
];
