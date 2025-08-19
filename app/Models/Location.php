<?php
namespace App\Models;
use PDO;

class Location {
  public static function all() {
    return DB::pdo()->query("SELECT * FROM locations ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
  }
  public static function find(int $id) {
    $st = DB::pdo()->prepare("SELECT * FROM locations WHERE id=?");
    $st->execute([$id]);
    return $st->fetch(PDO::FETCH_ASSOC) ?: null;
  }
  public static function create(array $d) {
    $st = DB::pdo()->prepare("INSERT INTO locations (nombre,tipo,descripcion) VALUES (?,?,?)");
    $st->execute([$d['nombre'], $d['tipo'] ?? 'Otro', $d['descripcion'] ?? null]);
    return (int)DB::pdo()->lastInsertId();
  }
}
