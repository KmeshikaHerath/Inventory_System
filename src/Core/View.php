<?php

namespace App\Core;

class View
{
    // Method to render a view with optional data
    public static function render($view, $data = []): string
    {
       extract($data);

       // Construct the full path to the view file
        $file = __DIR__ . '/../../Views/' . $view . '.php';

        // Check if the view file exists
        if (!file_exists($file)) {
            throw new \Exception("View file not found: {$file}");
        }

        ob_start();
        include $file;
        return ob_get_clean();

         // load layout
        include __DIR__ . "/../Views/layouts/main.php";
    }
}