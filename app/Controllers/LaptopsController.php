<?php
namespace App\Controllers;
use App\Models\{DB, Laptop, Location};

class LaptopsController {
  public function index() {
    $laptops = Laptop::all();
    return view('laptops/index', compact('laptops'));
  }

  public function create() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      csrf_check();
      $ubicacionId = ($_POST['ubicacion_id'] ?? '') !== '' ? (int)$_POST['ubicacion_id'] : null;
      Laptop::create([
        'num_serie'    => trim($_POST['num_serie']),
        'marca'        => trim($_POST['marca'] ?? ''),
        'modelo'       => trim($_POST['modelo'] ?? ''),
        'estado'       => $_POST['estado'] ?? 'disponible',
        'ubicacion_id' => $ubicacionId,
      ]);
      header('Location: ' . url('laptops/index')); exit;
    }
    $locations = Location::all();
    return view('laptops/create', compact('locations'));
  }

  public function edit() {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { http_response_code(400); echo 'ID requerido'; return; }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      csrf_check();
      $ubicacionId = ($_POST['ubicacion_id'] ?? '') !== '' ? (int)$_POST['ubicacion_id'] : null;
      Laptop::update($id, [
        'num_serie'    => trim($_POST['num_serie']),
        'marca'        => trim($_POST['marca'] ?? ''),
        'modelo'       => trim($_POST['modelo'] ?? ''),
        'estado'       => $_POST['estado'] ?? 'disponible',
        'ubicacion_id' => $ubicacionId,
      ]);
      header('Location: ' . url('laptops/index')); exit;
    }
    $laptop    = Laptop::find($id);
    $locations = Location::all();
    return view('laptops/edit', compact('laptop','locations'));
  }
}
