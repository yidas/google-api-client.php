<?php

// Bootstrap
require __DIR__ . '/../bootstrap.php';

use app\components\AppGoogleClient;
use yidas\google\apiHelper\services\People as PeopleHelper;

// Service bootstrap
$client = AppGoogleClient::authProcess(AppGoogleClient::$scopes['people']);

// Get Service
$service = PeopleHelper::setClient($client)
    ->getService();

try {

    $op = isset($_GET['op']) ? $_GET['op'] : null;
    
    switch ($op) {
        case 'add':
        case 'update':
            
            $name = isset($_POST['name']) ? $_POST['name'] : '';
            $email = isset($_POST['email']) ? $_POST['email'] : '';
            $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
            $resourceName = isset($_POST['id']) ? $_POST['id'] : null;
            $editMode = ($resourceName) ? true : false; 

            // Get person from API search to simple develop likes preventing eTag check
            $person = ($editMode) 
                ? $service->people->get($resourceName)
                : new Google_Service_PeopleService_Person;
            // var_dump($person);exit;

            // UpdatePersonFields mask is required for update
            $updatePersonFields = [];
            
            // Name
            $peopleName = new Google_Service_PeopleService_Name;
            if ($name) {
                // Add updatePersonFields
                $updatePersonFields[] = 'names';

                // First name
                $peopleName->setGivenName($name);
                    // Middle name
                $peopleName->setMiddleName('');
                // Last name
                $peopleName->setFamilyName('');
                $person->setNames($peopleName);
            }
            
            // eMail
            if ($email) {
                // Add updatePersonFields
                $updatePersonFields[] = 'emailAddresses';

                $emailAddress = new Google_Service_PeopleService_EmailAddress;
                $emailAddress->setValue($email);
                $person->setEmailAddresses($emailAddress);
            }
            
            // Phone
            if ($phone) {
                // Add updatePersonFields
                $updatePersonFields[] = 'phoneNumbers';

                $phoneNumber = new Google_Service_PeopleService_PhoneNumber;
                $phoneNumber->setValue($phone);
                $person->setPhoneNumbers($phoneNumber);
            }
            
            // print_r($person);exit;

            if ($editMode) {
                
                $result = $service->people->updateContact($resourceName, $person, [
                    'updatePersonFields' => $updatePersonFields
                ]);

            } else {

                $result = $service->people->createContact($person);
            }

            // Back to default route of this function 
            header("Location: {$_SERVER['PHP_SELF']}");
            exit;
            break;

        case 'delete':
            
            $resourceName = isset($_GET['id']) ? $_GET['id'] : null;

            if ($resourceName) {
                
                $result = $result = $service->people->deleteContact($resourceName);
                // var_dump($result);exit;
            }
            
            // Back to default route of this function 
            header("Location: {$_SERVER['PHP_SELF']}");
            exit;
            break;
        
        default:

            // Get formated list by Helper
            $contacts = PeopleHelper::getSimpleContacts();

            break;
    }
    
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

<h3><a href="./">Google API</a> - People</h3>

<form action="?op=add" method="post">
<input type="submit" value="Save" name="submit">
ID: <input type="text" name="id" size="25" placeholder="For Update" />
<input type="reset" value="Reset">
<br><br>
Name: <input type="text" name="name" size="10" />
eMail: <input type="text" name="email" size="20" />
Phone: <input type="text" name="phone" size="10" />
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
    <td><?=$key+1?></td>
    <td><?=$contact['name']?></td>
    <td><?=$contact['email']?></td>
    <td><?=$contact['phone']?></td>
    <td>
      <a href="javascript:edit('<?=$contact['id']?>', '<?=$contact['name']?>', '<?=$contact['email']?>', '<?=$contact['phone']?>')">Edit</a>
      <a href="javascript:if(confirm('Confirm delete?')){location.href='?op=delete&id=<?=$contact['id']?>'}">Delete</a>
    </td>
  </tr>
  <?php endforeach ?>
</table>

<script>
  function edit(id, name, email, phone) {
    document.getElementsByName("id")[0].value = id;
    document.getElementsByName("name")[0].value = name;
    document.getElementsByName("email")[0].value = email;
    document.getElementsByName("phone")[0].value = phone;
    document.getElementsByName("submit")[0].focus();
  }
</script>

</body>
</html>