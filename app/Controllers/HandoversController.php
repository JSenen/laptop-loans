<?php
namespace App\Controllers;
use App\Models\{DB, Person, Laptop, Course, Handover};
use App\Services\PdfService;

class HandoversController {
    public function index() {
        $history = Handover::history();
        return view('handovers/index', compact('history'));
    }

    public function entrega() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();

            $obs   = trim($_POST['observaciones'] ?? '');
            $fecha = $_POST['fecha'] ?: date('Y-m-d H:i:s');
            $courseId = ($_POST['course_id'] ?? '') !== '' ? (int)$_POST['course_id'] : null;

            // --- RUTA A: selects (nuevo flujo) ---
            if (!empty($_POST['person_id']) && !empty($_POST['laptop_id'])) {
                $personId = (int)$_POST['person_id'];
                $laptopId = (int)$_POST['laptop_id'];

                $last = Handover::lastForLaptop($laptopId);
                if ($last && $last['tipo'] === 'entrega') { die('Este portátil ya está prestado'); }

                DB::pdo()->beginTransaction();
                try {
                    $hid = Handover::create([
                        'laptop_id'       => $laptopId,
                        'person_id'       => $personId,
                        'course_id'       => $courseId,
                        'tipo'            => 'entrega',
                        'fecha'           => $fecha,
                        'observaciones'   => $obs,
                        'recibido_por'    => null,
                        'recibo_pdf_path' => null,
                    ]);
                    Laptop::setEstado($laptopId, 'prestado');

                    // Datos del movimiento
                    $row = DB::pdo()->query("SELECT h.id,h.tipo,h.fecha,h.observaciones, p.nombre,p.apellidos,p.dni,p.tip,p.telefono,p.email,
                                                    l.num_serie, c.nombre AS curso
                                              FROM handovers h
                                              JOIN people p ON p.id=h.person_id
                                              JOIN laptops l ON l.id=h.laptop_id
                                              LEFT JOIN courses c ON c.id=h.course_id
                                              WHERE h.id={$hid}")->fetch();

                    // Relleno para plantilla
                    $data = [
                        'curso' => $row['curso'] ?? '',
                        'nombre' => $row['nombre'],
                        'apellidos' => $row['apellidos'],
                        'dni' => $row['dni'],
                        'tip' => $row['tip'],
                        'telefono' => $row['telefono'],
                        'email' => $row['email'],
                        'equipo_descripcion' => 'Portátil',
                        'num_serie' => $row['num_serie'],
                        'fecha_entrega' => $row['fecha'],
                        'lugar' => '',
                        'firma_receptor_nombre' => $row['nombre'].' '.$row['apellidos'],
                        // Logos locales con file://
                        'logo_izq' => PdfService::fileUrl(BASE_PATH . '/public/assets/img/logo_izq.png'),
                        'logo_der' => PdfService::fileUrl(BASE_PATH . '/public/assets/img/logo_der.png'),
                    ];

                    // Generar + guardar
                    $tpl = BASE_PATH . "/recibos_templates/entrega.html";
                    $pdf = PdfService::renderTemplate($tpl, $data);
                    $saved = PdfService::savePdf($pdf, BASE_PATH . "/storage/recibos", "entrega_{$hid}.pdf");

                    DB::pdo()->prepare("UPDATE handovers SET recibo_pdf_path=? WHERE id=?")->execute([$saved, $hid]);
                    DB::pdo()->commit();
                } catch (\Throwable $e) { DB::pdo()->rollBack(); throw $e; }

                header('Location: ' . \url('handovers/index')); exit;
            }

            // --- RUTA B: fallback (DNI/TIP + Nº serie) ---
            $dni = trim($_POST['dni'] ?? '');
            $tip = trim($_POST['tip'] ?? '');
            if ($dni === '' && $tip === '') { die('Indica persona (select) o DNI/TIP.'); }

            $person = ($dni !== '') ? Person::findByDniOrTip($dni) : null;
            if (!$person && $tip !== '') { $person = Person::findByDniOrTip($tip); }
            if (!$person) {
                if ($dni === '') { die('La persona no existe. Crea la ficha primero en Personas (DNI obligatorio).'); }
                $personId = Person::create([
                    'nombre'    => $_POST['nombre']    ?? '',
                    'apellidos' => $_POST['apellidos'] ?? '',
                    'dni'       => $dni,
                    'tip'       => $tip ?: null,
                    'telefono'  => $_POST['telefono']  ?? null,
                    'email'     => $_POST['email']     ?? null,
                ]);
                $person = ['id' => (int)$personId];
            }

            $serie = trim($_POST['num_serie'] ?? '');
            $laptop = Laptop::findBySerie($serie);
            if (!$laptop) { die('Portátil no encontrado'); }

            $last = Handover::lastForLaptop((int)$laptop['id']);
            if ($last && $last['tipo'] === 'entrega') { die('Este portátil ya está prestado'); }

            DB::pdo()->beginTransaction();
            try {
                $hid = Handover::create([
                    'laptop_id'       => (int)$laptop['id'],
                    'person_id'       => (int)$person['id'],
                    'course_id'       => $courseId,
                    'tipo'            => 'entrega',
                    'fecha'           => $fecha,
                    'observaciones'   => $obs,
                    'recibido_por'    => null,
                    'recibo_pdf_path' => null,
                ]);
                Laptop::setEstado((int)$laptop['id'], 'prestado');

                $row = DB::pdo()->query("SELECT h.id,h.tipo,h.fecha,h.observaciones, p.nombre,p.apellidos,p.dni,p.tip,p.telefono,p.email,
                                                l.num_serie, c.nombre AS curso
                                          FROM handovers h
                                          JOIN people p ON p.id=h.person_id
                                          JOIN laptops l ON l.id=h.laptop_id
                                          LEFT JOIN courses c ON c.id=h.course_id
                                          WHERE h.id={$hid}")->fetch();

                $data = [
                    'curso' => $row['curso'] ?? '',
                    'nombre' => $row['nombre'],
                    'apellidos' => $row['apellidos'],
                    'dni' => $row['dni'],
                    'tip' => $row['tip'],
                    'telefono' => $row['telefono'],
                    'email' => $row['email'],
                    'equipo_descripcion' => 'Portátil',
                    'num_serie' => $row['num_serie'],
                    'fecha_entrega' => $row['fecha'],
                    'lugar' => '',
                    'firma_receptor_nombre' => $row['nombre'].' '.$row['apellidos'],
                    'logo_izq' => PdfService::fileUrl(BASE_PATH . '/public/assets/img/logo_izq.png'),
                    'logo_der' => PdfService::fileUrl(BASE_PATH . '/public/assets/img/logo_der.png'),
                ];

                $tpl = BASE_PATH . "/recibos_templates/entrega.html";
                $pdf = PdfService::renderTemplate($tpl, $data);
                $saved = PdfService::savePdf($pdf, BASE_PATH . "/storage/recibos", "entrega_{$hid}.pdf");

                DB::pdo()->prepare("UPDATE handovers SET recibo_pdf_path=? WHERE id=?")->execute([$saved, $hid]);
                DB::pdo()->commit();
            } catch (\Throwable $e) { DB::pdo()->rollBack(); throw $e; }

            header('Location: ' . \url('handovers/index')); exit;
        }

        // GET: listas para selects
        $people  = DB::pdo()->query("SELECT id,nombre,apellidos,dni,tip FROM people ORDER BY nombre,apellidos")->fetchAll();
        $laptops = DB::pdo()->query("SELECT id,num_serie FROM laptops WHERE estado='disponible' ORDER BY num_serie")->fetchAll();
        $courses = Course::all();
        return view('handovers/entrega', compact('people','laptops','courses'));
    }

    public function devolucion() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();

            $obs   = trim($_POST['observaciones'] ?? '');
            $fecha = $_POST['fecha'] ?: date('Y-m-d H:i:s');

            // --- RUTA A: select ---
            if (!empty($_POST['laptop_id'])) {
                $laptopId = (int)$_POST['laptop_id'];
                $last = Handover::lastForLaptop($laptopId);
                if (!$last || $last['tipo'] !== 'entrega') { die('Este portátil no está prestado'); }

                DB::pdo()->beginTransaction();
                try {
                    $hid = Handover::create([
                       'laptop_id'       => $laptopId,
                       'person_id'       => (int)$last['person_id'],
                       'course_id'       => $last['course_id'],
                       'tipo'            => 'devolucion',
                       'fecha'           => $fecha,
                       'observaciones'   => $obs,
                       'recibido_por'    => null,
                       'recibo_pdf_path' => null,
                    ]);
                    Laptop::setEstado($laptopId, 'disponible');

                    $row = DB::pdo()->query("SELECT h.id,h.tipo,h.fecha,h.observaciones, p.nombre,p.apellidos,p.dni,p.tip,p.telefono,p.email,
                                                    l.num_serie, c.nombre AS curso
                                              FROM handovers h
                                              JOIN people p ON p.id=h.person_id
                                              JOIN laptops l ON l.id=h.laptop_id
                                              LEFT JOIN courses c ON c.id=h.course_id
                                              WHERE h.id={$hid}")->fetch();

                    $data = [
                        'curso' => $row['curso'] ?? '',
                        'nombre' => $row['nombre'],
                        'apellidos' => $row['apellidos'],
                        'dni' => $row['dni'],
                        'tip' => $row['tip'],
                        'telefono' => $row['telefono'],
                        'email' => $row['email'],
                        'equipo_descripcion' => 'Portátil',
                        'num_serie' => $row['num_serie'],
                        'fecha_devolucion' => $row['fecha'],
                        'lugar' => '',
                        'firma_receptor_nombre' => $row['nombre'].' '.$row['apellidos'],
                        'logo_izq' => PdfService::fileUrl(BASE_PATH . '/public/assets/img/logo_izq.png'),
                        'logo_der' => PdfService::fileUrl(BASE_PATH . '/public/assets/img/logo_der.png'),
                    ];

                    $tpl = BASE_PATH . "/recibos_templates/devolucion.html";
                    $pdf = PdfService::renderTemplate($tpl, $data);
                    $saved = PdfService::savePdf($pdf, BASE_PATH . "/storage/recibos", "devolucion_{$hid}.pdf");

                    DB::pdo()->prepare("UPDATE handovers SET recibo_pdf_path=? WHERE id=?")->execute([$saved, $hid]);

                    DB::pdo()->commit();
                } catch (\Throwable $e) { DB::pdo()->RollBack(); throw $e; }

                header('Location: ' . \url('handovers/index')); exit;
            }

            // --- RUTA B: fallback nº serie ---
            $serie  = trim($_POST['num_serie'] ?? '');
            $laptop = Laptop::findBySerie($serie);
            if (!$laptop) { die('Portátil no encontrado'); }
            $last = Handover::lastForLaptop((int)$laptop['id']);
            if (!$last || $last['tipo']!=='entrega') { die('Este portátil no está prestado'); }

            DB::pdo()->beginTransaction();
            try {
                $hid = Handover::create([
                    'laptop_id'       => (int)$laptop['id'],
                    'person_id'       => (int)$last['person_id'],
                    'course_id'       => $last['course_id'],
                    'tipo'            => 'devolucion',
                    'fecha'           => $fecha,
                    'observaciones'   => $obs,
                    'recibido_por'    => null,
                    'recibo_pdf_path' => null,
                ]);
                Laptop::setEstado((int)$laptop['id'], 'disponible');

                $row = DB::pdo()->query("SELECT h.id,h.tipo,h.fecha,h.observaciones, p.nombre,p.apellidos,p.dni,p.tip,p.telefono,p.email,
                                                l.num_serie, c.nombre AS curso
                                          FROM handovers h
                                          JOIN people p ON p.id=h.person_id
                                          JOIN laptops l ON l.id=h.laptop_id
                                          LEFT JOIN courses c ON c.id=h.course_id
                                          WHERE h.id={$hid}")->fetch();

                $data = [
                    'curso' => $row['curso'] ?? '',
                    'nombre' => $row['nombre'],
                    'apellidos' => $row['apellidos'],
                    'dni' => $row['dni'],
                    'tip' => $row['tip'],
                    'telefono' => $row['telefono'],
                    'email' => $row['email'],
                    'equipo_descripcion' => 'Portátil',
                    'num_serie' => $row['num_serie'],
                    'fecha_devolucion' => $row['fecha'],
                    'lugar' => '',
                    'firma_receptor_nombre' => $row['nombre'].' '.$row['apellidos'],
                    'logo_izq' => PdfService::fileUrl(BASE_PATH . '/public/assets/img/logo_izq.png'),
                    'logo_der' => PdfService::fileUrl(BASE_PATH . '/public/assets/img/logo_der.png'),
                ];

                $tpl = BASE_PATH . "/recibos_templates/devolucion.html";
                $pdf = PdfService::renderTemplate($tpl, $data);
                $saved = PdfService::savePdf($pdf, BASE_PATH . "/storage/recibos", "devolucion_{$hid}.pdf");

                DB::pdo()->prepare("UPDATE handovers SET recibo_pdf_path=? WHERE id=?")->execute([$saved, $hid]);

                DB::pdo()->commit();
            } catch (\Throwable $e) { DB::pdo()->rollBack(); throw $e; }

            header('Location: ' . \url('handovers/index')); exit;
        }

        // GET: portátiles prestados con último receptor
        $prestados = DB::pdo()->query("
            SELECT l.id, l.num_serie, p.nombre, p.apellidos
            FROM laptops l
            JOIN handovers h ON h.laptop_id = l.id
            JOIN people p    ON p.id = h.person_id
            WHERE l.estado='prestado' AND h.tipo='entrega'
              AND h.id = (
                 SELECT h2.id FROM handovers h2
                 WHERE h2.laptop_id=l.id
                 ORDER BY h2.fecha DESC, h2.id DESC LIMIT 1
              )
            ORDER BY l.num_serie
        ")->fetchAll();
        return view('handovers/devolucion', compact('prestados'));
    }
}
