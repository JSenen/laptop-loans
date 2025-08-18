<?php
namespace App\Services;

use Dompdf\Dompdf;

class PdfService {
    private static function normalizeKey(string $k): string {
        $k = trim($k);
        $k = function_exists('mb_strtolower') ? mb_strtolower($k, 'UTF-8') : strtolower($k);
        if (function_exists('iconv')) {
            $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $k);
            if ($t !== false) $k = $t;
        }
        $k = preg_replace('/[^a-z0-9]+/', '_', $k);
        return trim(preg_replace('/_+/', '_', $k), '_');
    }

    public static function renderTemplate(string $templatePath, array $data, bool $debugSaveHtml=false, ?string $debugOutPath=null): string {
        $html = file_get_contents($templatePath);

        // normaliza claves: "Número de serie" => "numero_de_serie"
        $vars = [];
        foreach ($data as $k=>$v) $vars[self::normalizeKey((string)$k)] = (string)$v;

        $cb = function(array $m) use ($vars) {
            $key = self::normalizeKey($m[1]);
            $val = $vars[$key] ?? '';
            // no escapar rutas de imagen
            if (preg_match('~^(file|https?)://~i', $val)) return $val;
            return htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        };

        // {{ campo }} y [[ campo ]]
        $html = preg_replace_callback('/\{\{\s*([^}]+?)\s*\}\}/u', $cb, $html);
        $html = preg_replace_callback('/\[\[\s*([^\]]+?)\s*\]\]/u', $cb, $html);
        // {campo} (SOLO palabras/guiones_bajos para no coger CSS)
        $html = preg_replace_callback('/\{\s*([A-Za-z0-9_]+)\s*\}/u', $cb, $html);

        if ($debugSaveHtml && $debugOutPath) @file_put_contents($debugOutPath, $html);

        $dompdf = new Dompdf([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();
        return $dompdf->output();
    }

    public static function savePdf(string $binary, string $dir, string $filename): string {
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $path = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($path, $binary);
        return $path;
    }

    public static function fileUrl(string $path): string {
        $abs = realpath($path) ?: $path;
        $abs = str_replace('\\', '/', $abs);
        if (preg_match('~^[A-Za-z]:/~', $abs)) $abs = '/'.$abs; // -> /C:/...
        return 'file://' . $abs; // file:///C:/...
    }
}
