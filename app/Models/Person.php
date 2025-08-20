<?php
namespace App\Models;
use PDO;

class Person {
  public static function all(bool $soloActivos = true): array {
    $where = $soloActivos ? "WHERE activo=1" : "";
    $sql = "SELECT * FROM people $where ORDER BY apellidos, nombre";
    return DB::pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function find(int $id): ?array {
    $st = DB::pdo()->prepare("SELECT * FROM people WHERE id=?");
    $st->execute([$id]);
    return $st->fetch(PDO::FETCH_ASSOC) ?: null;
  }

  public static function create(array $d): int {
    $st = DB::pdo()->prepare("
      INSERT INTO people (nombre,apellidos,dni,tip,telefono,email,activo)
      VALUES (?,?,?,?,?,?,1)
    ");
    $st->execute([
      $d['nombre'] ?? '', $d['apellidos'] ?? '', $d['dni'] ?? '',
      $d['tip'] ?? null, $d['telefono'] ?? null, $d['email'] ?? null
    ]);
    return (int)DB::pdo()->lastInsertId();
  }

  public static function update(int $id, array $d): void {
    $st = DB::pdo()->prepare("
      UPDATE people SET nombre=?, apellidos=?, dni=?, tip=?, telefono=?, email=?
      WHERE id=?
    ");
    $st->execute([
      $d['nombre'] ?? '', $d['apellidos'] ?? '', $d['dni'] ?? '',
      $d['tip'] ?? null, $d['telefono'] ?? null, $d['email'] ?? null, $id
    ]);
  }

  public static function setActivo(int $id, bool $on): void {
    $st = DB::pdo()->prepare("UPDATE people SET activo=? WHERE id=?");
    $st->execute([$on ? 1 : 0, $id]);
  }

  // ya lo tenías, lo dejo por si acaso
  public static function findByDniOrTip(string $dniOrTip): ?array {
    $st = DB::pdo()->prepare("SELECT * FROM people WHERE dni=? OR tip=? LIMIT 1");
    $st->execute([$dniOrTip, $dniOrTip]);
    return $st->fetch(PDO::FETCH_ASSOC) ?: null;
  }
}
