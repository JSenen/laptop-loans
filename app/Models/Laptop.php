<?php
namespace App\Models;
use PDO;

class Laptop {
    public static function create(array $d): int {
        $sql="INSERT INTO laptops (num_serie, marca, modelo, estado, observaciones) VALUES (?,?,?,?,?)";
        DB::pdo()->prepare($sql)->execute([$d['num_serie'],$d['marca'],$d['modelo'],$d['estado'] ?? 'disponible',$d['observaciones'] ?? null]);
        return (int)DB::pdo()->lastInsertId();
    }
    public static function findBySerie(string $serie): ?array {
        $st=DB::pdo()->prepare("SELECT * FROM laptops WHERE num_serie=? LIMIT 1");
        $st->execute([$serie]); $r=$st->fetch(); return $r ?: null;
    }
    public static function setEstado(int $id, string $estado): void {
        DB::pdo()->prepare("UPDATE laptops SET estado=? WHERE id=?")->execute([$estado,$id]);
    }
    public static function all(): array {
        return DB::pdo()->query("SELECT * FROM laptops ORDER BY id DESC")->fetchAll();
    }
}