<?php
return [
    'host' => 'localhost',
    'dbname' => 'it_support_db',
    'username' => 'it_support_user',
    'password' => 'YourStrongPassword123!',  // ЗМІНІТЬ НА СВІЙ ПАРОЛЬ
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];