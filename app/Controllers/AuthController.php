<?php
namespace App\Controllers;

class AuthController {
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();
            $user = $_POST['user'] ?? '';
            $pass = $_POST['pass'] ?? '';
            // Simple auth: replace with DB users table if needed
            if ($user === 'admin' && $pass === 'admin') {
                $_SESSION['user'] = ['name' => 'Administrador'];
                header('Location: ' . \url('handovers/index'));
            }
            $error = "Credenciales inválidas";
            return view('auth/login', compact('error'));
        }
        return view('auth/login');
    }
    public function logout() {
        session_destroy();
        header('Location: /?r=auth/login'); exit;
    }
}