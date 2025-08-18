<?php
namespace App\Controllers;
use App\Models\Course;

class CoursesController {
    public function index() {
        $courses = Course::all();
        return view('courses/index', compact('courses'));
    }
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();
            $id = Course::create($_POST);
            header('Location: ' . \url('courses/index')); exit;
        }
        return view('courses/form');
    }
}