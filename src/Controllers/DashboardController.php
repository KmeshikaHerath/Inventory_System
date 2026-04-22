<?php

namespace App\Controllers;

use App\Core\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        session_start();

        if (!isset($_SESSION['user'])) {
            return new Response("<script>window.location='/login';</script>");
        }

        $user = $_SESSION['user'];

        $html = View::render('dashboard', [
            'user' => $user
        ]);

        return new Response($html);
    }
}