<?php
namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use App\Core\View; 

class HomeController
 {
    public static function home(): Response
    {
        return new Response("Welcome to the Home Page!");
    }

    // New method to render the home view
    public static function notifications(): Response
    {
        $html = View::render('home', [
            'title' => 'Home Page',
            'message' => 'Welcome to the View Page!'
        ]);

        return new Response($html);
    }

}
