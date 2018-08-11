<?php

namespace app\components;

use yidas\google\apiHelper\Client as ClientHelper;

class AppGoogleClient
{   
    /**
     * Google Service Scopes
     *
     * @var array
     */
    public static $scopes = [
        'plus' => [
            \Google_Service_Plus::USERINFO_PROFILE,
            \Google_Service_Plus::USERINFO_EMAIL,
        ],
        'calendar' => [\Google_Service_Calendar::CALENDAR],
        'drive' => [\Google_Service_Drive::DRIVE],
        'people' => [\Google_Service_PeopleService::CONTACTS],
        // 'contacts' => ["https://www.google.com/m8/feeds"],
        ];

    public static function getClient()
    {
        $config = include BASE_PATH . '/config/google.php';
        
        ClientHelper::setClient()
            ->setApplicationName('Google API')
            ->setAuthConfig($config['authConfig'])
            ->setRedirectUri($config['redirectUri'])
            // ->setScopes($config['serviceScopes']['plus'])
            ->setAccessType('offline')
            ->setApprovalPrompt('force');
            
        
        // Access Token
        $token = User::getToken();
        if ($token) {
	
            ClientHelper::setAccessToken($token);
            // Refresh the token if it's expired.
            if ($accessToken = ClientHelper::refreshAccessToken()) {
                
                User::saveToken($accessToken);
            }
        }
            
        return ClientHelper::getClient();;
    }
}
