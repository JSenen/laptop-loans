<?php
namespace App\Controllers;
use App\Models\Laptop;

class LaptopsController {
    public function index() {
        $laptops = Laptop::all();
        return view('laptops/index', compact('laptops'));
    }
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();
            $id = Laptop::create($_POST);
            header('Location: ' . \url('laptops/index')); exit;
        }
        return view('laptops/form');
    }
}