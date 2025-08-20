<?php

define('DEBUG_PDF', true);

$configFile = __DIR__ . '/config.local.php';
if (!file_exists($configFile)) $configFile = __DIR__ . '/config.example.php';
$CONFIG = require $configFile;



$CONFIG['auth'] = [
  'provider' => 'helpdesk',
  'helpdesk' => [
    'dsn'  => 'mysql:host=127.0.0.1;dbname=Hesk;charset=utf8mb4',
    'user' => 'gati',
    'pass' => '3-Aminavana',

    'table'  => 'hesk_users',            // <-- si tu tabla se llama distinto, cámbialo
    'fields' => [
      'id'       => 'id',
      'username' => 'user',
      'email'    => 'email',
      'password' => 'pass',
      'name'     => 'name',
      'isadmin'  => 'isadmin',      // opcional, para roles
    ],

    'password_strategy' => 'password_hash', // bcrypt ($2y$)
    'only_admins' => false,                 // pon true si solo quieres permitir isadmin=1
  ],
];

