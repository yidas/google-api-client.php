<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../components/GoogleApiModel.php';

// print_r($_GET);
// print_r($_SERVER);exit;

// Session init
if (session_status() == PHP_SESSION_NONE) {

    session_start();
}

$callback = "http://{$_SERVER['HTTP_HOST']}" . dirname($_SERVER['PHP_SELF']) . "/callback.php";
$credentialPath = __DIR__ . '/../files/google_api_secret.json';

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

// Set Access Token
$token = GoogleApiModel::getToken();
// print_r($token);exit;

if ($token) {

	$client->setAccessToken($token);
	// Refresh the token if it's expired.
	if ($client->isAccessTokenExpired()) {

		$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
	}

	// Plus Service
	$servicePlus = new Google_Service_Plus($client);
	$me = $servicePlus->people->get('me');
	// Get default email
	$me['email'] = $me['emails'][0]->value;
	
} else {

	// Not login
	$authUrl = $client->createAuthUrl();
}

/**
 * Route: Operation
 */
if (isset($_GET['op'])) {

	switch ($_GET['op']) {

		case 'register':

			$_SESSION['register_flag'] = true;

			header("Location: {$authUrl}");

			break;

		case 'register_services':

			$_SESSION['register_flag'] = true;

			header("Location: {$authServicesUrl}");

			break;

		// Logout Controller
		case 'logout':
		default:
			// Delete Access Token by Model
			GoogleApiModel::deleteToken();

			header('Location: ./');
			break;
	}

	return;
}



?>

<!DOCTYPE html>
<html>
<head>
  <title></title>
</head>
<body>

  <h3>Google API Services Sample</h3>

<?php if (isset($authUrl)): ?>

  Status: you are not login

  <hr/>

  <dl>
    <dt><h3><a href="<?=$authUrl?>">Login</a></h3></dt>
      <dd>
        This will not work because you don't register yet, the function could be implement for User-GoogleID database mapping.
      </dd>
    <dt><h3><a href="./?op=register">Register</a></h3></dt>
      <dd>
        Register for basic scopes for login information, which will save AccessToken.
      </dd>
    <dt><h3><a href="./?op=register_services">Register-Services</a></h3></dt>
      <dd>
        Register for services such as Calendar or Drive, which will save AccessToken. </br>
        This is base on register but adding more scopes.
      </dd>
  </dl>

<?php else: ?>

  <ul>
    <li><a href="calendar.php">Google Calendar</a></li>
    <li><a href="drive.php">Google Drive</a></li>
  </ul>

  <p>
  * You need to Register-Services then you will have permission to access Services.
  </p>

  <hr/>

  <h4>Login User Profile</h4>

  <dl>
    <dt>Google ID:</dt>
      <dd><?=$me['id']?></dd>
    <dt>Display Name:</dt>
      <dd><?=$me['displayName']?></dd>
    <dt>Image Url:</dt>
      <dd><a href="<?=$me['image']['url']?>" target="_blank"><?=$me['image']['url']?></a></dd>
    <dt>Plus Url:</dt>
      <dd><a href="<?=$me['url']?>" target="_blank"><?=$me['url']?></a></dd>
    <dt>Email:</dt>
      <dd><a href="mailto:<?=$me['email']?>" target="_blank"><?=$me['email']?></a></dd>
    <dt>AccessToken:</dt>
      <dd><?=$accessToken?></dd>
  </dl>

  <hr/>

  <a href="<?=$authUrl?>">Re-Login</a> | <a href="./?op=register">Register</a> | <a href="./?op=register_services">Register-Services</a> | <a href="./?op=logout">Logout</a>

<?php endif ?>
  
</body>
</html>