<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Composer autoload

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

// Load routes
$routes = require __DIR__ . '/../src/route.php';

// Create Request object from global PHP variables
$request = Request::createFromGlobals();

// Create the context using the current request
$context = new RequestContext();
$context->fromRequest($request);

// Create the URL matcher
$matcher = new UrlMatcher($routes, $context);

try {
    $parameters = $matcher->match($request->getPathInfo());

    $controller = $parameters['_controller'];
    unset($parameters['_controller'], $parameters['_route']);

   
   
   
    $response = call_user_func_array($controller, $parameters);

} catch (ResourceNotFoundException $e) {
    $response = new Response('404 Not Found', 404);
   
} catch (Exception $e) {
    $response = new Response('An error occurred: ' . $e->getMessage(), 500);
    $response->send();
} finally {
    // This always runs, even if an exception occurs
    if (isset($response)) {
        $response->prepare($request);
        $response->send();
    }
}