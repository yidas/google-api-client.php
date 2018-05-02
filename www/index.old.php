<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../components/GoogleAPI.php';
require_once __DIR__ . '/../components/GoogleApiModel.php';

// print_r($_GET);
// print_r($_SERVER);exit;

// Session init
if (session_status() == PHP_SESSION_NONE) {

    session_start();
}

/**
 * Default Client for login info only without Scopes access
 *
 * Set Client with configuration and accessToken(if exist)
 */
$authUrl = GoogleAPI::setClient([
			'redirectUri' => 'http://' . $_SERVER['HTTP_HOST'].'/google-api/www',
			])
		->setAccessToken(GoogleApiModel::getToken())
		->createAuthUrl();

/**
 * Add Scopes for link type login then you can get access token with Scopes.
 */
$authServicesUrl = GoogleAPI::addScopes([
			Google_Service_Calendar::CALENDAR,
			Google_Service_Drive::DRIVE,
			])
		->setClient([
			'redirectUri' => 'http://' . $_SERVER['HTTP_HOST'].'/google-api/www',
			])
		->setAccessToken(GoogleApiModel::getToken())
		->createAuthUrl();	

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

/**
 * Route: OAuth Callback
 */
if (isset($_GET['code'])) {
	
	$accessToken = GoogleAPI::authorize($_GET['code'])->getAccessToken();

	// Save AccessToken if is register
	if (isset($_SESSION['register_flag']) && $_SESSION['register_flag']) {
		
		$result = GoogleApiModel::saveToken($accessToken);

		unset($_SESSION['register_flag']);
	}

	header('Location: ./');

	return;
}

/**
 * Is Login
 */
if (GoogleAPI::isAuth()) {

	/**
	 * Profile
	 */
	$servicePlus = GoogleAPI::getService('Google_Service_Plus');
	$me = $servicePlus->people->get('me');
	// Get default email
	$me['email'] = $me['emails'][0]->value;
	// print_r($me['emails']);exit;

	// print "ID: {$me['id']}<br>";
	// print "Display Name: {$me['displayName']}<br>";
	// print "Image Url: {$me['image']['url']}<br>";
	// print "Url: {$me['url']}<br>";
	// print($me['emails'][0]->value);<br>";

	$accessToken = json_encode( GoogleApiModel::getToken() );
}

?>

<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>

	<h3>Google API Services Demo</h3>

<?php if (!GoogleAPI::isAuth()): ?>

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