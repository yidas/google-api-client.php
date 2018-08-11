<?php

// Bootstrap
require __DIR__ . '/../bootstrap.php';

use app\components\AppGoogleClient;
use app\components\User;

/**
 * OAuth Callback
 */
if (isset($_GET['error'])) {
	// Redirect to index
	header('Location: ./');
}

$code = isset($_GET['code']) ? $_GET['code'] : null;
if (!$code) {
    throw new Exception("Google API Callback code is required", 400);
}

// Client
$client = AppGoogleClient::getClient();

// Authorization
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
