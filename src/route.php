<?php 
use Symfony\Component\Routing\Route; 
use Symfony\Component\Routing\RouteCollection; 
use App\Controllers\HomeController; 

$routes = new RouteCollection(); 

$routes->add('home', new Route('/',['_controller' => [HomeController::class, 'home']], [],['GET']));

return $routes;