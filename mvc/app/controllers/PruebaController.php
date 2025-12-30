<?php
namespace App\Controllers;

use App\Core\Controller;

class PruebaController extends Controller
{
    public function index()
    {
        $this->loadView('modules/prueba');
    }
}
