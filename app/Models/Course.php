<?php
namespace App\Models;
use PDO;

class Course {
    public static function create(array $d): int {
        $sql="INSERT INTO courses (nombre, descripcion, fecha_inicio, fecha_fin) VALUES (?,?,?,?)";
        DB::pdo()->prepare($sql)->execute([$d['nombre'],$d['descripcion'] ?? null,$d['fecha_inicio'] ?? null,$d['fecha_fin'] ?? null]);
        return (int)DB::pdo()->lastInsertId();
    }
    public static function all(): array {
        return DB::pdo()->query("SELECT * FROM courses ORDER BY nombre ASC")->fetchAll();
    }
}