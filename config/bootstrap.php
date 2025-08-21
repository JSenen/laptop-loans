<?php
require __DIR__ . '/config.php';

// 👇 Composer autoload (NECESARIO para Dompdf)
$composerAutoload = BASE_PATH . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require $composerAutoload;
} else {
    http_response_code(500);
    echo "Falta vendor/autoload.php. Ejecuta 'composer install' o 'composer require dompdf/dompdf:^2' en " . BASE_PATH;
    exit;
}




spl_autoload_register(function($class){
    $prefix = 'App\\';
    $base_dir = BASE_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) require $file;
});

function view(string $view, array $params = []): string {
    $path = BASE_PATH . "/app/Views/$view.php";
    if (!file_exists($path)) return "View not found: $view";
    extract($params);
    $__view = $view; // ✅ pasar el nombre de vista al layout
    ob_start();
    include BASE_PATH . "/app/Views/layouts/base.php";
    return ob_get_clean();
}


function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_check() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $t = $_POST['csrf'] ?? '';
        if (!$t || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
            http_response_code(419); die('CSRF token mismatch');
        }
    }
}

function base_url(): string {
    global $CONFIG;
    $b = $CONFIG['app']['base_url'] ?? '';
    // sin barra final
    return rtrim($b, '/');
}
function url(string $route): string {
    // genera /laptop-loans/public/?r=people/index
    return base_url() . '/?r=' . $route;
}
function asset(string $path): string {
    // para CSS/JS: asset('assets/css/app.css')
    return base_url() . '/' . ltrim($path, '/');
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' .
           htmlspecialchars(csrf_token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') .
           '">';
}
function pagination_links(int $total, int $page, int $perPage, string $route, array $extra = []): string {
    $pages = max(1, (int)ceil($total / $perPage));
    if ($pages <= 1) return '';
    $html = '<nav aria-label="Paginación"><ul class="pagination">';

    $qbase = url($route) . ($extra ? '&' . http_build_query($extra) : '');
    $link  = fn($p) => $qbase . '&page=' . $p;

    // Prev
    $disabled = $page <= 1 ? ' disabled' : '';
    $html .= '<li class="page-item'.$disabled.'"><a class="page-link" href="'.($page>1?$link($page-1):'#').'">&laquo;</a></li>';

    // Números (ventana de 5)
    $start = max(1, $page - 2);
    $end   = min($pages, $page + 2);
    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="'.$link(1).'">1</a></li>';
        if ($start > 2) $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
    }
    for ($i=$start; $i<=$end; $i++) {
        $active = $i===$page ? ' active' : '';
        $html .= '<li class="page-item'.$active.'"><a class="page-link" href="'.$link($i).'">'.$i.'</a></li>';
    }
    if ($end < $pages) {
        if ($end < $pages-1) $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
        $html .= '<li class="page-item"><a class="page-link" href="'.$link($pages).'">'.$pages.'</a></li>';
    }

    // Next
    $disabled = $page >= $pages ? ' disabled' : '';
    $html .= '<li class="page-item'.$disabled.'"><a class="page-link" href="'.($page<$pages?$link($page+1):'#').'">&raquo;</a></li>';

    return $html.'</ul></nav>';


}


    //--- FUNCION FORMATO FECHA 
    function df(?string $date, string $fmt = 'd-m-Y'): string {
    if (!$date) return '';
    $ts = strtotime($date);
    return $ts ? date($fmt, $ts) : '';
    }


