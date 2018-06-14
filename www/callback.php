<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../components/User.php';

// Configuration
$config = require __DIR__ . '/../config.inc.php';

// print_r($_SERVER);exit;
// echo $callback;exit;

/**
 * OAuth Callback
 */

$code = isset($_GET['code']) ? $_GET['code'] : null;
if (!$code) {
    throw new Exception("Google API Callback code is required", 400);
}

$client = new Google_Client();
$client->setApplicationName('Google API');
// $client->setScopes([
// 	Google_Service_Plus::USERINFO_PROFILE,
// 	Google_Service_Plus::USERINFO_EMAIL,
// 	// Google_Service_Calendar::CALENDAR,
// 	// Google_Service_Drive::DRIVE,
// 	]);
$client->setAuthConfig($config['authConfig']);
$client->setRedirectUri($config['redirectUri']);
$client->setAccessType('offline');
$client->setApprovalPrompt('force'); 

// Aithorization
$accessToken = $client->fetchAccessTokenWithAuthCode($code);
// print_r($accessToken);exit;

$result = User::saveToken($accessToken);

// Service register
$service = User::getRegisterService();
if ($service == 'all') {
	
	// Google Service Scopes
	$serviceScopes = $config['serviceScopes'];

	foreach ($serviceScopes as $key => $service) {
		
		User::addService($key);
	}
} 
elseif ($service) {

	User::addService($service);
}
// Remove service register
User::registerService(null);


// Redirect to index
header('Location: ./');
