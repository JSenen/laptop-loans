<?php
namespace App\Models;
use PDO;

class Person {
    public static function create(array $d): int {
        // ✅ No permitimos crear sin DNI
        $dni = trim($d['dni'] ?? '');
        if ($dni === '') {
            die('DNI obligatorio para crear persona (ve a Personas → + Nueva persona).');
        }

        $sql = "INSERT INTO people (nombre, apellidos, dni, tip, telefono, email) VALUES (?,?,?,?,?,?)";
        DB::pdo()->prepare($sql)->execute([
            $d['nombre']    ?? '',
            $d['apellidos'] ?? '',
            $dni,
            $d['tip']       ?? null,
            $d['telefono']  ?? null,
            $d['email']     ?? null,
        ]);
        return (int)DB::pdo()->lastInsertId();
    }

    public static function findByDniOrTip(string $dniTip): ?array {
        $st = DB::pdo()->prepare("SELECT * FROM people WHERE dni=? OR tip=? LIMIT 1");
        $st->execute([$dniTip, $dniTip]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function all(): array {
        return DB::pdo()->query("SELECT * FROM people ORDER BY id DESC")->fetchAll();
    }
}
