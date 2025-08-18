<?php
$configFile = __DIR__ . '/config.local.php';
if (!file_exists($configFile)) $configFile = __DIR__ . '/config.example.php';
$CONFIG = require $configFile;