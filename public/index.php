<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Composer autoload

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

date_default_timezone_set('Asia/Colombo');

//Load .env file
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Load routes
$routes = require __DIR__ . '/../src/route.php';

// Captures http request
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

    // Call the controller and get the response
    $response = call_user_func_array($controller, $parameters);

} catch (ResourceNotFoundException $e) {
    $response = new Response('404 Not Found', 404);
   
} catch (Exception $e) {
    $response = new Response('An error occurred: ' . $e->getMessage(), 500);
    
    //Sends output to the browser and ends the script execution
    $response->send();

} finally {
    // This always runs, even if an exception occurs
    if (isset($response)) {
        $response->prepare($request);
        $response->send();
    }
}  