<?php 
use Symfony\Component\Routing\Route; 
use Symfony\Component\Routing\RouteCollection; 
use App\Controllers\HomeController; 
use App\Controllers\UserController;
use App\Controllers\LoginController;
use App\Controllers\DashboardController;


$routes = new RouteCollection(); 

$routes->add('home', new Route('/',['_controller' => [HomeController::class, 'home']], [],['GET']));

$routes->add('view', new Route('/views',['_controller' => [HomeController::class, 'notifications']], [],  ['GET'] ));

$routes->add('show_form', new Route('/register', ['_controller' => [UserController::class, 'index']], [], ['GET']));

$routes->add('user_store', new Route('/register-user', ['_controller' => [UserController::class, 'register']], [], ['POST']));

$routes->add('login_form', new Route('/login', ['_controller' => [LoginController::class, 'showLoginForm']] , [], ['GET']));

$routes->add('login_submit', new Route('/login_user', ['_controller' => [LoginController::class, 'login']], [], [], '', [], ['POST']));

$routes->add('dashboard', new Route('/dashboard', ['_controller' => [DashboardController::class, 'index']], [], ['GET']));

return $routes;