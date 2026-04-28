<?php

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\LoginController;
use App\Controllers\DashboardController;
use App\Controllers\ProductController;

$routes = new RouteCollection();

$routes->add('home', new Route('/', ['_controller' => [HomeController::class, 'home']], [], ['GET']));

$routes->add('view', new Route('/views', ['_controller' => [HomeController::class, 'notifications']], [],  ['GET']));

$routes->add('show_form', new Route('/register', ['_controller' => [UserController::class, 'index']], [], ['GET']));

$routes->add('user_store', new Route('/register-user', ['_controller' => [UserController::class, 'register']], [], ['POST']));

$routes->add('login_form', new Route('/login', ['_controller' => [LoginController::class, 'showLoginForm']], [], ['GET']));

$routes->add('login_submit', new Route('/login_user', ['_controller' => [LoginController::class, 'login']], [], [], '', [], ['POST']));

$routes->add('dashboard', new Route('/dashboard', ['_controller' => [DashboardController::class, 'index']], [], ['GET']));

$routes->add('logout', new Route('/logout', ['_controller' => [LoginController::class, 'logout']], [], ['GET']));

// View page
$routes->add('products', new Route('/products',['_controller' => [ProductController::class, 'index']],[],[],'',[],['GET']));

$routes->add('store_product', new Route('/products/store',['_controller' => [ProductController::class, 'store']],[],[],'',[],['POST']));

// Paginate (AJAX)
$routes->add('products_paginate', new Route('/products/paginate',['_controller' => [ProductController::class, 'paginate']],[],[],'',[],['GET']));

// Delete (AJAX)
$routes->add('delete_product', new Route('/products/delete',['_controller' => [ProductController::class, 'delete']],[],[],'',[],['POST']));

// Get single product (for edit modal)
$routes->add('get_product', new Route('/products/get',['_controller' => [ProductController::class, 'get']],[],[],'',[],['GET']));

// Update
$routes->add('update_product', new Route('/products/update',['_controller' => [ProductController::class, 'update']],[],[],'',[],['POST']));

$routes->add('restore_product',new Route('/products/restore',['_controller' => [ProductController::class, 'restore']],[],[],'',[],['POST']));
return $routes;
