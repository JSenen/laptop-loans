<?php
namespace App\Controllers;
use App\Models\{Course, DB};

class CoursesController {
  public function index() {
    $show = $_GET['show'] ?? 'active'; // active|archived|all
    $courses = ($show === 'all') ? Course::all(false)
             : ($show === 'archived'
                ? array_filter(Course::all(false), fn($c)=> (int)$c['activo']===0)
                : Course::all(true));
    return view('courses/index', compact('courses','show'));
  }

  public function create() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      csrf_check();
      Course::create([
        'nombre'       => trim($_POST['nombre']),
        'fecha_inicio' => $_POST['fecha_inicio'] ?? null,
        'fecha_fin'    => $_POST['fecha_fin'] ?? null,
      ]);
      header('Location: ' . url('courses/index')); exit;
    }
    return view('courses/create');
  }

  public function edit() {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { http_response_code(400); echo "ID requerido"; return; }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      csrf_check();
      Course::update($id, [
        'nombre'       => trim($_POST['nombre']),
        'fecha_inicio' => $_POST['fecha_inicio'] ?? null,
        'fecha_fin'    => $_POST['fecha_fin'] ?? null,
      ]);
      header('Location: ' . url('courses/index')); exit;
    }
    $course = Course::find($id);
    if (!$course) { http_response_code(404); echo "Curso no encontrado"; return; }
    return view('courses/edit', compact('course'));
  }

  public function archive() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo "Método no permitido"; return; }
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) { http_response_code(400); echo "ID requerido"; return; }
    Course::setActivo($id, false);
    header('Location: ' . url('courses/index')); exit;
  }

  public function restore() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo "Método no permitido"; return; }
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) { http_response_code(400); echo "ID requerido"; return; }
    Course::setActivo($id, true);
    header('Location: ' . url('courses/index')); exit;
  }
}
