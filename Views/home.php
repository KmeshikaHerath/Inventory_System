<?php

        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title><?= htmlspecialchars($title?? 'Default Title', ENT_QUOTES, 'UTF-8'); ?></title>
        </head>
        <body>
            <h1><?= htmlspecialchars($message ?? 'No message provided', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p>This is a View page.</p>
        </body>
        </html>
    