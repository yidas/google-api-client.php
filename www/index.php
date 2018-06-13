<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../components/User.php';

// Configuration
$config = require __DIR__ . '/../config.inc.php';
// Google Service Scopes
$serviceScopes = [
    'plus' => [
        Google_Service_Plus::USERINFO_PROFILE,
        Google_Service_Plus::USERINFO_EMAIL,
    ],
    'calendar' => [Google_Service_Calendar::CALENDAR],
    'drive' => [Google_Service_Drive::DRIVE],
];

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
          // Add Scopes
          $scopes = array_merge($serviceScopes['plus'], $serviceScopes[$service]);
          $client->setScopes($scopes);
          $authServicesUrl = $client->createAuthUrl();	

          header("Location: {$authServicesUrl}");

          break;

      // Logout Controller
      case 'logout':
      default:
          // Delete Access Token by Model
          User::deleteToken();

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

		$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
	}

	// Plus Service
  $servicePlus = new Google_Service_Plus($client);
  
  try {

    $me = $servicePlus->people->get('me');
    // Get default email
    $me['email'] = $me['emails'][0]->value;
    
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
    <li><a href="calendar.php">Google Calendar</a> (<a href="./?op=register_service&service=calendar">Register Access</a>)</li>
    <li><a href="drive.php">Google Drive</a> (<a href="./?op=register_service&service=drive">Register Access</a>)</li>
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

  <a href="<?=$authUrl?>">Re-Login</a> | <a href="./?op=register">Register</a> | <a href="./?op=register_services">Register-Services</a> | <a href="./?op=logout">Logout</a>

<?php endif ?>
  
</body>
</html>