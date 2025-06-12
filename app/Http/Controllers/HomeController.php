<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    // HomeController.php
    public function index()
    {
        return view('dashboard');
    }
}
