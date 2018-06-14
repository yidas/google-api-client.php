<?php

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
	
	die('Composer is not set, run `composer install` in the app root.');
}

require_once $autoloadPath;
require_once __DIR__ . '/../components/User.php';

// Configuration
$config = require __DIR__ . '/../config.inc.php';

// Check
if (!file_exists($config['authConfig'])) {
	
	die("Please set up Google credential file in {$config['authConfig']}");
}


// Google Service Scopes
$serviceScopes = $config['serviceScopes'];

// print_r($_GET);
// print_r($_SERVER);exit;

// Session init
if (session_status() == PHP_SESSION_NONE) {
	
	session_start();
}

// Client
$client = new Google_Client();
$client->setApplicationName('Google API');
$client->setScopes($serviceScopes['plus']);
$client->setAuthConfig($config['authConfig']);
$client->setRedirectUri($config['redirectUri']);
$client->setAccessType('offline');
$client->setApprovalPrompt('force'); 

/**
* Route: Operation
*/
if (isset($_GET['op'])) {
	
	switch ($_GET['op']) {
		
		case 'register':
		
			$authUrl = $client->createAuthUrl();
			header("Location: {$authUrl}");
			
			break;
		
		case 'register_service':
		
			$service = (isset($_GET['service'])) ? $_GET['service'] : null;
			
			if (!$service || !isset($serviceScopes[$service])) {
				
				echo 'Service not found';exit;
			}

			// Original services
			$services = User::getServices();
			foreach ($services as $key => $myService) {
				$client->addScope($serviceScopes[$myService]);
			}

			// Add Scopes
			$client->addScope($serviceScopes[$service]);
			User::registerService($service);

			$authServicesUrl = $client->createAuthUrl();	
			header("Location: {$authServicesUrl}");
			break;
			
		case 'deregister_service':

			$service = (isset($_GET['service'])) ? $_GET['service'] : null;
			
			if (!$service || !isset($serviceScopes[$service])) {
				
				echo 'Service not found';exit;
			}

			User::removeService($service);
			
			header("Location: ./");
			break;
		
		case 'register_services':

			// Register all services
			foreach ($serviceScopes as $key => $service) {
				
				$client->addScope($service);
			}

			User::registerService('all');

			$authServicesUrl = $client->createAuthUrl();	
			header("Location: {$authServicesUrl}");
			break;
		
		// Logout Controller
		case 'logout':
		
			// Reset all user data
			User::resetData();
		
		default:
		
			header('Location: ./');
			break;
	}
	
	return;
}

// Set Access Token
$token = User::getToken();
// print_r($token);exit;

if ($token) {
	
	$client->setAccessToken($token);
	// Refresh the token if it's expired.
	if ($client->isAccessTokenExpired()) {
		
		$token = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
		User::saveToken($token);
	}
	
	// Plus Service
	$servicePlus = new Google_Service_Plus($client);
	
	try {
		
		$me = $servicePlus->people->get('me');
		// Get default email
		$me['email'] = $me['emails'][0]->value;

		// Owned services
		$services = User::getServices();
		
	} catch (\Google_Service_Exception $e) {
		
		$errors = print_r($e->getErrors(), true);
		echo '<a href="./?op=logout">Logout</a><br/>';
		echo "You got errors for {$e->getCode()} Status Code and the details are below:";
		echo "<pre>{$errors}</pre>";
		exit;
		
	}
	
	$accessToken = json_encode(User::getToken(), JSON_PRETTY_PRINT);
	
} else {
	
	// Not login
	$authUrl = $client->createAuthUrl();
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
</br>
User Session: <?=session_id()?>

<hr/>

<p>This sample code assume you are already identitied by your session ID, you would lost identity without the keeping session.</p>

<dl>
<dt><h3><a href="./?op=register">Register</a></h3></dt>
<dd>
Register for basic scopes for login information.
</dd>
<dt><h3><a href="./?op=register_services">Register all Services</a></h3></dt>
<dd>
Register all the Google services such as Calendar or Drive. </br>
This is base on register but adding all the service scopes.
</dd>
</dl>

<?php else: ?>

<ul>
<li>
	<a href="calendar.php">Google Calendar</a> 
	<?php if(in_array('calendar', $services)):?>
		(<a href="./?op=deregister_service&service=calendar">Deregister Access</a>)
	<?php else: ?>
		(<a href="./?op=register_service&service=calendar">Register Access</a>)
	<?php endif ?>
</li>
<li>
	<a href="drive.php">Google Drive</a> 
	<?php if(in_array('drive', $services)):?>
		(<a href="./?op=deregister_service&service=drive">Deregister Access</a>)
	<?php else: ?>
		(<a href="./?op=register_service&service=drive">Register Access</a>)
	<?php endif ?>
</li>
<li>
	<a href="contacts.php">Google Contacts</a> 
	<?php if(in_array('contacts', $services)):?>
		(<a href="./?op=deregister_service&service=contacts">Deregister Access</a>)
	<?php else: ?>
		(<a href="./?op=register_service&service=contacts">Register Access</a>)
	<?php endif ?>
</li>
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
<dd><pre><?=$accessToken?></pre></dd>
</dl>

<hr/>

<a href="./?op=register">Register Again (Services will gone)</a> | <a href="./?op=register_services">Register-All-Services</a> | <a href="./?op=logout">Logout</a>

<?php endif ?>

</body>
</html>