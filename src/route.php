<?php 
use Symfony\Component\Routing\Route; 
use Symfony\Component\Routing\RouteCollection; 
use App\Controllers\HomeController; 
use App\Controllers\BlogController;

$routes = new RouteCollection(); 

$routes->add('home', new Route('/',['_controller' => [HomeController::class, 'home']], [],['GET']));

$routes->add('view', new Route('/views',['_controller' => [HomeController::class, 'notifications']], [],  ['GET'] ));

return $routes;