<?php
declare(strict_types=1);
session_start();

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/config/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\PeopleController;
use App\Controllers\LaptopsController;
use App\Controllers\CoursesController;
use App\Controllers\HandoversController;
use App\Controllers\ReceiptsController;
use App\Controllers\ExportsController;
use App\Controllers\LocationsController;


// Simple router (?r=controller/action)
$r = $_GET['r'] ?? 'dashboard/index';
list($controller, $action) = array_pad(explode('/', $r), 2, 'index');

$map = [
  'auth'      => AuthController::class,
  'people'    => PeopleController::class,
  'laptops'   => LaptopsController::class,
  'courses'   => CoursesController::class,
  'handovers' => HandoversController::class,
  'dashboard' => HandoversController::class, 
  'receipts'  => ReceiptsController::class,
  'exports'   => ExportsController::class,
  'locations' => LocationsController::class,
];

if (!isset($map[$controller])) { http_response_code(404); echo "404"; exit; }
$ctrl = new $map[$controller]();

if (!method_exists($ctrl, $action)) { http_response_code(404); echo "404"; exit; }

// Require login except auth/*
// $publicRoutes = ['auth/login','auth/logout'];
// if (!in_array("$controller/$action", $publicRoutes) && empty($_SESSION['user'])) {
//     header('Location: ' . url('auth/login'));

//     exit;
// }

echo $ctrl->$action();