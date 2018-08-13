<?php

// Bootstrap
require __DIR__ . '/../bootstrap.php';

use app\components\AppGoogleClient;
use app\components\GoogleCalendar;

// Service bootstrap
$client = AppGoogleClient::authProcess(AppGoogleClient::$scopes['calendar']);

// Get Service for initializing GoogleCalendar
// $service = GoogleAPI::getService('Google_Service_Calendar');
$service = new Google_Service_Calendar($client);

// GoogleCalendar Component
GoogleCalendar::init($service);

try {

	/**
	 * Operation
	 */
	if (isset($_GET['op'])) {
		
		switch ($_GET['op']) {
			case 'addedit':
				
				addEditController();

				break;
			
			default:
				echo '404 - Bad Operation';
				break;
		}

		return;
	}


	/**
	 * Index
	 */

	// Get calendar lists
	$calendarList = GoogleCalendar::calendarList();

	// Get calendar lists, which contain self canlendar only
	$myCalendarList = GoogleCalendar::calendarList(true);

	/**
	 * Get Event List
	 */
	$calendarID = isset($_GET['id']) ? urldecode($_GET['id']) : 'primary';

	try {

		$events = GoogleCalendar::eventList($calendarID);
		
	} catch (Exception $e) {
		
		// Detect Calendar 404 Error
		$errorMessgae = ($e->getCode()==404) 
			? "Calendar not found" 
			: $e->getMessage();

		throw new Exception($errorMessgae, $e->getCode());
	}
	
	// print_r($events);exit;

} catch (Exception $e) {
	
	switch ($e->getCode()) {
		case '403':
			echo 'Access Denied: You don\'t have permissions of this Service';
			break;
		
		default:
			echo 'Error-'.$e->getCode().' : '.$e->getMessage();
			break;
	}

	exit;
}

/**
 * Contorller: Add & Edit
 */
function addEditController()
{
	try {

		$calendarID = isset($_GET['id']) ? urldecode($_GET['id']) : 'primary';
		

		/* DateTime handler demo */

		date_default_timezone_set('Asia/Taipei'); 

		$isoNow = date(DateTime::ISO8601);

		$isoTomorrow = date(DateTime::ISO8601, (time()+86400));


		/**
		 * Insert
		 */
		$eventID = GoogleCalendar::eventInsert([
				'summary' => 'Demo by GoogleAPI - Insert',
				'location' => 'Default Location',
				'description' => 'For description.',
				'start' => [
					'dateTime' => '2017-05-28T09:00:00',
					'timeZone' => 'Asia/Taipei',
				],
				'end' => [
					'dateTime' => '2017-05-29T17:00:00+08',
					// 'dateTime' => '2017-05-29T17:00:00',
					// 'timeZone' => 'Asia/Taipei',
				],
			], $calendarID);

		/**
		 * Update
		 */
		$event = GoogleCalendar::eventGet($eventID);
		// print_r($event->getStart());throw new Exception("Error Processing Request", 1);
		
		$event->setSummary('Demo by GoogleAPI - Edit');

		// Set via Google_Service_Calendar_EventDateTime
		$dateObject = new Google_Service_Calendar_EventDateTime();
		$dateObject->setDateTime($isoNow);
		$event->start = $dateObject;

		// Set directly
		$event->end->dateTime = $isoTomorrow;

		$updateAt = GoogleCalendar::eventUpdate($event);
		// print_r($updateAt);

		/**
		 * Delete
		 */		
		// $result = GoogleCalendar::eventDelete($eventID);print_r($result);exit;

	} catch (Exception $e) {
		
		throw $e;
	}

	echo "Operation Success!<br/> Updated at {$updateAt}<br/> <a href=\"?\">Back to List Page</a>";
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Google API - Calendar</title>
</head>
<body>

	<h3><a href="./">Google API</a> - Calendar</h3>

	<ul>
		<li><a href="?op=addedit&id=<?= urlencode($calendarID) ?>">Add&Edit an Event</a></li>
	</ul>

	<hr/>

	All Calendars: 
	<ul>
	<?php foreach ($calendarList as $calendarID => $calendarTitle): ?>
		<li><a href="?id=<?= urlencode($calendarID) ?>"><?=$calendarTitle?></a></li>
	<?php endforeach ?>
	</ul>

	My Calendars: 
	<ul>
	<?php foreach ($myCalendarList as $calendarID => $calendarTitle): ?>
		<li><a href="?id=<?= urlencode($calendarID) ?>"><?=$calendarTitle?></a></li>
	<?php endforeach ?>
	</ul>

	<hr/>

	Events:
	<ul>
	<?php foreach ((array)$events as $key => $event): ?>
		<li><?=$event['title']?>
		<ul>
			<li><?=$event['start']?></li>
			<li><?=$event['end']?></li>	
		</ul>
		</li>
	<?php endforeach ?>
	</ul>

</body>
</html>





