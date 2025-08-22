<?php
namespace App\Controllers;
use App\Models\{DB, Laptop, Location};



class LaptopsController {

private array $usos = ['General','Competencias Digitales','Alumnos','Formación','Administración','Otro'];
    
 public function index() {
  $show = $_GET['show'] ?? 'available'; // available|loaned|baja|all
  $estado = $show==='available'?'disponible':($show==='loaned'?'prestado':($show==='baja'?'baja':null));


  $page = max(1, (int)($_GET['page'] ?? 1));
  $perPage = 25; $offset = ($page-1)*$perPage;

  $where = $estado ? "WHERE l.estado=?" : "";
  $countSql = "SELECT COUNT(*) FROM laptops l $where";
  $st = DB::pdo()->prepare($countSql);
  if ($estado) $st->execute([$estado]); else $st->execute();
  $total = (int)$st->fetchColumn();

  $sql = "SELECT l.*, loc.nombre AS ubicacion
          FROM laptops l
          LEFT JOIN locations loc ON loc.id=l.ubicacion_id
          $where
          ORDER BY l.num_serie
          LIMIT ? OFFSET ?";
  $st = DB::pdo()->prepare($sql);
  $i = 1;
  if ($estado) { $st->bindValue($i++, $estado); }
  $st->bindValue($i++, $perPage, \PDO::PARAM_INT);
  $st->bindValue($i++, $offset,  \PDO::PARAM_INT);
  $st->execute();
  $laptops = $st->fetchAll();

  return view('laptops/index', compact('laptops','show','total','page','perPage'));
}


   public function create() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      csrf_check();
      $ubicacionId = ($_POST['ubicacion_id'] ?? '') !== '' ? (int)$_POST['ubicacion_id'] : null;
      Laptop::create([
        'num_serie'      => trim($_POST['num_serie']),
        'marca'          => trim($_POST['marca'] ?? ''),
        'modelo'         => trim($_POST['modelo'] ?? ''),
        'uso_preferente' => $_POST['uso_preferente'] ?? null,
        'estado'         => $_POST['estado'] ?? 'disponible',
        'ubicacion_id'   => $ubicacionId,
      ]);
      header('Location: ' . url('laptops/index')); exit;
    }
    $locations = Location::all();
    $usos = $this->usos;
    return view('laptops/create', compact('locations','usos'));
  }

  public function edit() {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { http_response_code(400); echo 'ID requerido'; return; }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      csrf_check();
      $ubicacionId = ($_POST['ubicacion_id'] ?? '') !== '' ? (int)$_POST['ubicacion_id'] : null;
      Laptop::update($id, [
        'num_serie'      => trim($_POST['num_serie']),
        'marca'          => trim($_POST['marca'] ?? ''),
        'modelo'         => trim($_POST['modelo'] ?? ''),
        'uso_preferente' => $_POST['uso_preferente'] ?? null,
        'estado'         => $_POST['estado'] ?? 'disponible',
        'ubicacion_id'   => $ubicacionId,
      ]);
      header('Location: ' . url('laptops/index')); exit;
    }
    $laptop    = Laptop::find($id);
    $locations = Location::all();
    $usos = $this->usos;
    return view('laptops/edit', compact('laptop','locations','usos'));
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
