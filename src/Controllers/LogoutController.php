<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;

class LogoutController
{
    public static function logout(): Response
    {
        $session = new Session();
        $session->start();

        // Clear session completely (recommended)
        $session->invalidate();

          return new RedirectResponse('/login');
    }
}
