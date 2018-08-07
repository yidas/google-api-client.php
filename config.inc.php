<?php

return [
    'redirectUri' => "http://{$_SERVER['HTTP_HOST']}" . dirname($_SERVER['PHP_SELF']) . "/callback.php",
    'authConfig' => __DIR__ . '/files/google_api_secret.json',
    // Google Service Scopes
    'serviceScopes' => [
        'plus' => [
            Google_Service_Plus::USERINFO_PROFILE,
            Google_Service_Plus::USERINFO_EMAIL,
        ],
        'calendar' => [Google_Service_Calendar::CALENDAR],
        'drive' => [Google_Service_Drive::DRIVE],
        'people' => [Google_Service_PeopleService::CONTACTS],
        // 'contacts' => ["https://www.google.com/m8/feeds"],
    ],
];