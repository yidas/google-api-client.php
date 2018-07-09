<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../components/User.php';

// Configuration
$config = require __DIR__ . '/../config.inc.php';

$token = User::getToken();
if (!User::getToken()) {
    
    header('Location: ./');
}

$client = new Google_Client();
$client->setApplicationName('Google API');
$client->setAuthConfig($config['authConfig']);
$client->setRedirectUri($config['redirectUri']);
$client->setAccessType('offline');
$client->setApprovalPrompt('force');
$client->setAccessToken($token);
// Refresh the token if it's expired.
if ($client->isAccessTokenExpired()) {
    
    $token = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    User::saveToken($token);
}

// Get Service
$service = new Google_Service_PeopleService($client);

try {
    
    
    // Print the names for up to 10 connections.
    $optParams = array(
        'pageSize' => 0,
        'personFields' => 'names,emailAddresses,phoneNumbers',
    );
	$results = $service->people_connections->listPeopleConnections('people/me', $optParams);
	
	// Parser
	$contacts = [];
    
    if (count($results->getConnections()) != 0) {
        
        foreach ($results->getConnections() as $person) {

			$data = [];
            $data['name'] = isset($person->getNames()[0]) 
                ? $person->getNames()[0]->getDisplayName() 
                : null;
            $data['email'] = isset($person->getEmailAddresses()[0]) 
                ? $person->getEmailAddresses()[0]->getValue() 
                : null;
            $data['phone'] = isset($person->getPhoneNumbers()[0]) 
                ? $person->getPhoneNumbers()[0]->getValue() 
                : null;
            // print_r($data['email']);

			$contacts[] = $data;
        }
	}
	// print_r($contacts);exit;
    
} catch (Exception $e) {

	switch ($e->getCode()) {
		case '403':
			echo 'Access Denied: You don\'t have permissions';
			break;
		
		default:
			echo $e->getMessage();
			break;
	}
	
	exit;
}
        
?>
        
<!DOCTYPE html>
<html>
<head>
<title>Google API - Drive</title>

<style>
  td {padding: 5px;}
</style>
</head>
<body>

<h3><a href="./">Google API</a> - Drive</h3>

<form action="?op=add" method="post">
Name: <input type="text" name="name" size="10" />
eMail: <input type="text" name="email" size="20" />
Phone: <input type="text" name="phone" size="10" />
<input type="submit" value="Add" name="submit">
</form>

<hr/>

<table border="1" style="border: solid 1px gray; border-collapse: collapse;">
  <thead>
    <td>Index</td>
    <td>Name</td>
    <td>eMail</td>
    <td>Phone</td>
    <td>Function</td>
  </thead>
  <?php foreach ($contacts as $key => $contact): ?>
  <tr>
    <td><?=$key?></td>
    <td><?=$contact['name']?></td>
    <td><?=$contact['email']?></td>
    <td><?=$contact['phone']?></td>
    <td></td>
  </tr>
  <?php endforeach ?>
</table>


</body>
</html>