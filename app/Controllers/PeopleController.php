<?php
namespace App\Controllers;
use App\Models\Person;

class PeopleController {
    public function index() {
        $people = Person::all();
        return view('people/index', compact('people'));
    }
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();
            $id = Person::create($_POST);
            header('Location: ' . \url('people/index')); exit;
        }
        return view('people/form');
    }
}