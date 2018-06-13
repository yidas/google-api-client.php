<?php

return [
    'redirectUri' => "http://{$_SERVER['HTTP_HOST']}" . dirname($_SERVER['PHP_SELF']) . "/callback.php",
    'authConfig' => __DIR__ . '/files/google_api_secret.json',
];