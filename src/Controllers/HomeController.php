<?php
namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;

class HomeController {

    public function home() {
        return new Response("Welcome to the Home Page!");
    }
}