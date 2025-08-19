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

