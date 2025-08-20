<?php
namespace App\Controllers;
use App\Models\{Person, DB};

class PeopleController {
  public function index() {
    $show = $_GET['show'] ?? 'active';  // active|archived|all
    $soloActivos = $show === 'active' ? true : ($show === 'archived' ? false : false);
    $people = ($show === 'all') ? Person::all(false)
            : ($show === 'archived'
                ? array_filter(Person::all(false), fn($p)=> (int)$p['activo']===0)
                : Person::all(true));
    return view('people/index', compact('people','show'));
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
