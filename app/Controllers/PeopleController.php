<?php
namespace App\Controllers;
use App\Models\{Person, DB};

class PeopleController {
  public function index() {
  $show = $_GET['show'] ?? 'active'; // active|archived|all
  $where = $show==='active' ? "WHERE activo=1" : ($show==='archived' ? "WHERE activo=0" : "");
  $page = max(1, (int)($_GET['page'] ?? 1));
  $perPage = 25;
  $offset = ($page-1) * $perPage;

  $total = (int)DB::pdo()->query("SELECT COUNT(*) FROM people $where")->fetchColumn();
  $st = DB::pdo()->prepare("SELECT * FROM people $where ORDER BY apellidos,nombre LIMIT ? OFFSET ?");
  $st->bindValue(1, $perPage, \PDO::PARAM_INT);
  $st->bindValue(2, $offset, \PDO::PARAM_INT);
  $st->execute();
  $people = $st->fetchAll();

  return view('people/index', compact('people','show','total','page','perPage'));
}

  public function create() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      csrf_check();
      Person::create([
        'nombre'    => trim($_POST['nombre']),
        'apellidos' => trim($_POST['apellidos']),
        'dni'       => trim($_POST['dni']),
        'tip'       => trim($_POST['tip'] ?? ''),
        'telefono'  => trim($_POST['telefono'] ?? ''),
        'email'     => trim($_POST['email'] ?? ''),
        'unidad_destino' => trim($_POST['unidad_destino'] ?? ''),
      ]);
      header('Location: ' . url('people/index')); exit;
    }
    return view('people/create');
  }

  public function edit() {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { http_response_code(400); echo "ID requerido"; return; }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      csrf_check();
      Person::update($id, [
        'nombre'    => trim($_POST['nombre']),
        'apellidos' => trim($_POST['apellidos']),
        'dni'       => trim($_POST['dni']),
        'tip'       => trim($_POST['tip'] ?? ''),
        'telefono'  => trim($_POST['telefono'] ?? ''),
        'email'     => trim($_POST['email'] ?? ''),
        'unidad_destino' => trim($_POST['unidad_destino'] ?? ''),
      ]);
      header('Location: ' . url('people/index')); exit;
    }
    $person = Person::find($id);
    if (!$person) { http_response_code(404); echo "Persona no encontrada"; return; }
    return view('people/edit', compact('person'));
  }

  public function archive() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo "Método no permitido"; return; }
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) { http_response_code(400); echo "ID requerido"; return; }
    Person::setActivo($id, false);
    header('Location: ' . url('people/index')); exit;
  }

  public function restore() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo "Método no permitido"; return; }
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) { http_response_code(400); echo "ID requerido"; return; }
    Person::setActivo($id, true);
    header('Location: ' . url('people/index')); exit;
  }
}
