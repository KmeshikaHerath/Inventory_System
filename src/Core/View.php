<?php

namespace App\Core;

class View
{
    public static function render($view, $data = [])
    {
       extract($data);

        $file = __DIR__ . '/../../Views/' . $view . '.php';

        if (!file_exists($file)) {
            throw new \Exception("View file not found: {$file}");
        }

        ob_start();
        include $file;
        return ob_get_clean();
    }
}