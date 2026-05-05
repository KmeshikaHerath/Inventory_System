<?php

namespace App\Controllers;

use App\Core\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Core\Logger;

/**
 * Class DashboardController
 *
 * Handles dashboard rendering after login
 */
class DashboardController
{
    /**
     * Show dashboard page
     *
     * @return Response
     */
    public static function index(): Response
    {
        try {
            
            if (!isset($_SESSION['user'])) {
                return new RedirectResponse('/login');
            }

            $user = $_SESSION['user'];

            $html = View::render('dashboard', ['user' => $user], true);

            return new Response($html);
        } catch (\Exception $e) {

            // Optional logging
            Logger::error('Dashboard error', ['message' => $e->getMessage()]);

            // Fallback response
            return new Response(
                '<h1>500 - Internal Server Error</h1>',
                500
            );
        }
    }
}
