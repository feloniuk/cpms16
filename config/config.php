<?php
return [
    'app' => [
        'name' => 'IT Support Bot',
        'version' => '1.0.0',
        'debug' => true,
        'timezone' => 'Europe/Kiev',
        'base_url' => 'https://cpms16.online/',  // ЗМІНІТЬ НА СВІЙ ДОМЕН
        'api_url' => 'https://cpms16.online/api'
    ],
    'notifications' => [
        'admin_notifications' => true,
        'status_change_notifications' => false
    ],
    'pagination' => [
        'per_page' => 20
    ],
    'logs' => [
        'enabled' => true,
        'level' => 'INFO',
        'max_files' => 30,
        'path' => __DIR__ . '/../logs/'
    ],
    'language' => 'uk'  // Українська мова
];