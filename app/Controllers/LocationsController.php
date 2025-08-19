<?php
namespace App\Controllers;

use App\Models\Location;

class LocationsController {
  public function index() {
    $locations = Location::all();
    return view('locations/index', compact('locations'));
  }
  public function create() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      csrf_check();
      Location::create([
        'nombre'      => trim($_POST['nombre']),
        'tipo'        => $_POST['tipo'] ?? 'Otro',
        'descripcion' => $_POST['descripcion'] ?? null,
      ]);
      header('Location: ' . url('locations/index')); exit;
    }
    return view('locations/create');
  }
}
