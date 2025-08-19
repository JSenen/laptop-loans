<?php
// Copy this to config.php and edit credentials
return [
  'db' => [
    'host' => '127.0.0.1',
    'port' => 3306,
    'name' => 'laptop_loans',
    'user' => 'gati', // TODO user 
    'pass' => '3-Aminavana', // TODO password
    'charset' => 'utf8mb4',
  ],
  'app' => [
    'name' => 'Gestor de Portátiles',
    'base_url' => '/laptop-loans/public',  // adjust if in subdir
    'app' => ['base_url' => '/laptop-loans/public']

  ],
];