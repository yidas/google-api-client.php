<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../components/GoogleApiModel.php';

$callback = "http://{$_SERVER['HTTP_HOST']}" . dirname($_SERVER['PHP_SELF']) . "/callback.php";
$credentialPath = __DIR__ . '/../files/google_api_secret.json';

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
$client->setAuthConfig($credentialPath);
$client->setRedirectUri($callback);
$client->setAccessType('offline');
$client->setApprovalPrompt('force'); 

// Aithorization
$accessToken = $client->fetchAccessTokenWithAuthCode($code);
// print_r($accessToken);exit;

$result = GoogleApiModel::saveToken($accessToken);

// Redirect to index
header('Location: ./');
