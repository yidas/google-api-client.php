<?php

namespace app\components;

use app\components\User;
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

    /**
     * Authorized process
     *
     * @param array $scopes
     * @return GOOGLE_Client 
     */
    public static function authProcess($scopes=[])
    {
        // User token check
        $token = User::getToken();
        if (!User::getToken()) {
            
            header('Location: ./');
        }

        // Client
        $client = self::getClient();

        // Set AccessToken into Google_Client
        ClientHelper::setAccessToken($token);

        // Token auto check
        if ($accessToken = ClientHelper::refreshAccessToken()) {
            User::saveToken($accessToken);
        }

        // Check Scopes
        if ($scopes) {
            
            if (!ClientHelper::verifyScopes($scopes)) {
                die("You don't have permission for this service scopes");
            }
        }
        
        return $client;
    }
}
