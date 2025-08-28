<?php
// app/Controllers/AiController.php
namespace App\Controllers;

use App\Services\AiService;
use App\Models\DB;

class AiController {

  /** Intenta clasificar con LLM; si falla, usa heurística en español */
  private function classify(string $q): array {
    // --- Paso A: pedir al LLM SOLO JSON (español + few-shot) ---
    $tools = [
      'WHO_HAS_LAPTOP' => 'Si preguntan quién tiene un portátil/ordenador, devuelve EXACTAMENTE: {"tool":"WHO_HAS_LAPTOP","serie":"<número_de_serie>"}',
      'COURSE_SUMMARY' => 'Si piden resumen/estado de un curso, devuelve EXACTAMENTE: {"tool":"COURSE_SUMMARY","course":"<nombre_o_id>"}',
      'OVERDUE'        => 'Si piden equipos atrasados/no devueltos, devuelve EXACTAMENTE: {"tool":"OVERDUE","days":<número_de_días>}',
      'LAPTOP_HISTORY' => 'Si piden historial de un portátil, devuelve EXACTAMENTE: {"tool":"LAPTOP_HISTORY","serie":"<número_de_serie>"}',
      'PERSON_STATUS'  => 'Si preguntan qué tiene una persona (por nombre/DNI/TIP), devuelve EXACTAMENTE: {"tool":"PERSON_STATUS","persona":"<texto_busqueda>"}',
    ];
    $fewshot = [
      'Q: ¿Quién tiene ahora el portátil PW03PA30?',
      'A: {"tool":"WHO_HAS_LAPTOP","serie":"PW03PA30"}',
      'Q: Resumen del curso Competencias Digitales 2025',
      'A: {"tool":"COURSE_SUMMARY","course":"Competencias Digitales 2025"}',
      'Q: Dame los que llevan más de 14 días sin devolver',
      'A: {"tool":"OVERDUE","days":14}',
      'Q: Historial del portátil 129PROMO',
      'A: {"tool":"LAPTOP_HISTORY","serie":"129PROMO"}',
      'Q: ¿Qué equipos tiene Juan Pérez DNI 12345678A?',
      'A: {"tool":"PERSON_STATUS","persona":"Juan Pérez DNI 12345678A"}',
    ];
    $prompt = "Elige una herramienta. Devuelve SOLO el JSON exacto, sin texto adicional.\n"
            . implode("\n", array_map(fn($k)=>"$k: ".$tools[$k], array_keys($tools)))
            . "\n\nEjemplos:\n".implode("\n",$fewshot)
            . "\n\nPregunta: {$q}\nRespuesta JSON:";
    $raw = AiService::chat([['role'=>'user','content'=>$prompt]]);
    $cmd = json_decode(trim($raw), true);
    if (isset($cmd['tool'])) return $cmd;

    // --- Paso B: Heurística en español si el LLM no acertó ---
    $qLower = mb_strtolower($q,'UTF-8');

    // 1) Serie (alfa-num guiones) tras palabras clave
    if (preg_match('/(qu[ié]n|quien).*(port[aá]til|ordenador|equipo).*?([A-Z0-9\-]{4,})/iu', $q, $m)) {
      return ['tool'=>'WHO_HAS_LAPTOP','serie'=>$m[3]];
    }
    if (preg_match('/historial.*(port[aá]til|ordenador|equipo).*?([A-Z0-9\-]{4,})/iu', $q, $m)) {
      return ['tool'=>'LAPTOP_HISTORY','serie'=>$m[2]];
    }

    // 2) Resumen curso: cualquier frase con “curso” o “resumen”
    if (preg_match('/(curso|resumen|estado).+?([A-Za-z0-9 _\-\/]{3,})/iu', $q, $m)) {
      return ['tool'=>'COURSE_SUMMARY','course'=>trim($m[2])];
    }

    // 3) Atrasados: buscar días o semanas
    if (preg_match('/(atrasad|vencid|pendient|sin devolver|no devuelt)/iu', $qLower)) {
      $days = 14;
      if (preg_match('/(\d+)\s*(d[ií]as?)/iu', $qLower, $m))   $days = (int)$m[1];
      elseif (preg_match('/(\d+)\s*seman/iu', $qLower, $m))    $days = (int)$m[1]*7;
      elseif (preg_match('/(\d+)\s*mes/iu', $qLower, $m))      $days = (int)$m[1]*30;
      return ['tool'=>'OVERDUE','days'=>$days];
    }

    // 4) Persona: “qué tiene <nombre|dni|tip>”
    if (preg_match('/(qu[eé]\s*tiene|asignado|prestado).*?([A-Za-zÁÉÍÓÚÜÑñ0-9\- ]{3,})/iu', $q, $m)) {
      return ['tool'=>'PERSON_STATUS','persona'=>trim($m[2])];
    }

    return ['tool'=>'UNKNOWN'];
  }

  public function ask() {
    $q = trim($_GET['q'] ?? '');
    $out = '';
    if ($q === '') return view('ai/ask', compact('q','out'));

    $cmd = $this->classify($q);

    switch ($cmd['tool'] ?? 'UNKNOWN') {
      case 'WHO_HAS_LAPTOP':
        $serie = $cmd['serie'] ?? '';
        $st = DB::pdo()->prepare("
          SELECT p.nombre,p.apellidos,p.dni,c.nombre AS curso,h.fecha
          FROM handovers h
          JOIN people p ON p.id=h.person_id
          LEFT JOIN courses c ON c.id=h.course_id
          JOIN laptops l ON l.id=h.laptop_id
          WHERE l.num_serie=?
          ORDER BY h.fecha DESC, h.id DESC LIMIT 1
        ");
        $st->execute([$serie]); $r=$st->fetch();
        $out = $r ? "Lo tiene **{$r['nombre']} {$r['apellidos']}** (DNI {$r['dni']}) desde {$r['fecha']}. Curso: ".($r['curso'] ?? '—')."."
                  : "No encuentro movimientos para el nº de serie **{$serie}**.";
        break;

      case 'COURSE_SUMMARY':
        $term = $cmd['course'] ?? '';
        // intentar por id o por nombre aproximado
        $c = DB::pdo()->prepare("SELECT id,nombre FROM courses WHERE id=? OR nombre LIKE ? LIMIT 1");
        $c->execute([(int)$term, '%'.$term.'%']); $course=$c->fetch();
        if (!$course) { $out="Curso no encontrado."; break; }
        $cnt = DB::pdo()->prepare("
          SELECT SUM(tipo='entrega') entregas, SUM(tipo='devolucion') devoluciones
          FROM (
            SELECT h.tipo FROM handovers h
            WHERE h.course_id=?
            AND h.id=(SELECT h2.id FROM handovers h2 WHERE h2.laptop_id=h.laptop_id AND h2.course_id=h.course_id ORDER BY h2.fecha DESC,h2.id DESC LIMIT 1)
          ) t
        ");
        $cnt->execute([(int)$course['id']]); $k=$cnt->fetch();
        $out = "Curso **{$course['nombre']}** → Entregados: ".((int)$k['entregas']).", Devueltos: ".((int)$k['devoluciones']).".";
        break;

      case 'OVERDUE':
        $days = max(1, (int)($cmd['days'] ?? 14));
        $st = DB::pdo()->prepare("
          SELECT l.num_serie,p.nombre,p.apellidos,h.fecha
          FROM handovers h
          JOIN laptops l ON l.id=h.laptop_id
          JOIN people  p ON p.id=h.person_id
          WHERE h.tipo='entrega'
            AND TIMESTAMPDIFF(DAY,h.fecha,NOW())>?
            AND h.id=(SELECT h2.id FROM handovers h2 WHERE h2.laptop_id=h.laptop_id ORDER BY h2.fecha DESC,h2.id DESC LIMIT 1)
          ORDER BY h.fecha
        ");
        $st->execute([$days]); $rows = $st->fetchAll();
        $out = $rows ? "Atrasados (> {$days} días):\n".implode("\n", array_map(fn($r)=>"- {$r['num_serie']} → {$r['nombre']} {$r['apellidos']} ({$r['fecha']})", $rows))
                     : "Sin atrasos (> {$days} días).";
        break;

      case 'LAPTOP_HISTORY':
        $serie = $cmd['serie'] ?? '';
        $st = DB::pdo()->prepare("
          SELECT h.tipo,h.fecha,c.nombre AS curso,p.nombre,p.apellidos
          FROM handovers h
          JOIN laptops l ON l.id=h.laptop_id
          LEFT JOIN courses c ON c.id=h.course_id
          JOIN people p ON p.id=h.person_id
          WHERE l.num_serie=?
          ORDER BY h.fecha DESC, h.id DESC
        ");
        $st->execute([$serie]); $rows=$st->fetchAll();
        $out = $rows ? "Historial de **{$serie}**:\n".implode("\n", array_map(fn($r)=>"- {$r['fecha']} · {$r['tipo']} · {$r['nombre']} {$r['apellidos']} · ".($r['curso']??'—'), $rows))
                     : "Sin historial para **{$serie}**.";
        break;

      case 'PERSON_STATUS':
        $term = $cmd['persona'] ?? '';
        $st = DB::pdo()->prepare("
          SELECT l.num_serie,h.tipo,h.fecha,c.nombre AS curso
          FROM people p
          JOIN handovers h ON h.person_id=p.id
          JOIN laptops l ON l.id=h.laptop_id
          LEFT JOIN courses c ON c.id=h.course_id
          WHERE p.nombre LIKE ? OR p.apellidos LIKE ? OR p.dni LIKE ? OR p.tip LIKE ?
          ORDER BY h.fecha DESC
        ");
        $like='%'.$term.'%';
        $st->execute([$like,$like,$like,$like]); $rows=$st->fetchAll();
        if (!$rows) { $out = "No encuentro movimientos para **{$term}**."; break; }
        // último estado por portátil
        $bySerie=[];
        foreach($rows as $r){ $k=$r['num_serie']; if(!isset($bySerie[$k])) $bySerie[$k]=$r; }
        $items = array_map(fn($r)=>"- {$r['num_serie']} → {$r['tipo']} ({$r['fecha']}) · ".($r['curso']??'—'), array_values($bySerie));
        $out = "Equipos vinculados a **{$term}**:\n".implode("\n",$items);
        break;

      default:
        $out = "No entendí la petición. Ejemplos: \n"
             . "• ¿Quién tiene el portátil PW03PA30?\n"
             . "• Resumen del curso Competencias Digitales 2025\n"
             . "• Portátiles atrasados más de 14 días\n"
             . "• Historial del portátil 129PROMO\n"
             . "• ¿Qué equipos tiene Juan Pérez 12345678A?";
    }

    return view('ai/ask', compact('q','out'));
  }
}
