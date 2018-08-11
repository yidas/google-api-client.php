<?php

return [
    'redirectUri' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}" . dirname($_SERVER['PHP_SELF']) . "/callback.php",
    'authConfig' => BASE_PATH . '/files/google_api_secret.json',
];