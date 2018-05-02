<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../components/GoogleApiModel.php';

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
$client->setScopes([
	Google_Service_Plus::USERINFO_PROFILE,
	Google_Service_Plus::USERINFO_EMAIL,
	// Google_Service_Calendar::CALENDAR,
	// Google_Service_Drive::DRIVE,
	]);
$client->setAuthConfig($config['authConfig']);
$client->setRedirectUri($config['redirectUri']);
$client->setAccessType('offline');
$client->setApprovalPrompt('force'); 

// Aithorization
$accessToken = $client->fetchAccessTokenWithAuthCode($code);
// print_r($accessToken);exit;

$result = GoogleApiModel::saveToken($accessToken);

// Redirect to index
header('Location: ./');
