<?php
namespace App\Controllers;
use App\Models\{DB, Laptop, Location};

class LaptopsController {
  public function index() {
    // filtros: available | loaned | baja | all
    $show = $_GET['show'] ?? 'available';
    $estado = null;
    if ($show === 'available') $estado = 'disponible';
    elseif ($show === 'loaned') $estado = 'prestado';
    elseif ($show === 'baja') $estado = 'baja';

    $laptops = Laptop::all($estado);
    return view('laptops/index', compact('laptops','show'));
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

  public function archive() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo "Método no permitido"; return; }
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) { http_response_code(400); echo "ID requerido"; return; }

    $lap = Laptop::find($id);
    if (!$lap) { http_response_code(404); echo "Portátil no encontrado"; return; }
    if ($lap['estado'] === 'prestado') { http_response_code(400); echo "No puedes dar de baja un portátil prestado"; return; }

    Laptop::setEstado($id, 'baja');
    header('Location: ' . url('laptops/index') . '&show=baja'); exit;
  }

  public function restore() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo "Método no permitido"; return; }
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) { http_response_code(400); echo "ID requerido"; return; }

    Laptop::setEstado($id, 'disponible');
    header('Location: ' . url('laptops/index') . '&show=available'); exit;
  }

  public function delete() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo "Método no permitido"; return; }
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) { http_response_code(400); echo "ID requerido"; return; }

    if (!Laptop::canDelete($id)) { http_response_code(400); echo "No se puede borrar: tiene histórico de movimientos"; return; }
    Laptop::delete($id);
    header('Location: ' . url('laptops/index')); exit;
  }
}
