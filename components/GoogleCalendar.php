<?php

/**
* Google Calendar API Component
*
* @author   Nick Tsai <myintaer@gmail.com>
* @version 	1.0.0
* @see 		Composer: google/apiclient:^2.0
* @link 	https://developers.google.com/google-apps/calendar/v3/reference/
*/
class GoogleCalendar
{
	/**
	 * @var Google Calendar Service Object
	 */
	private static $service;

	/**
	 * Initialize
	 *
	 * @param object $service Google Service (Get from GoogleAPI::getService())
	 */
	function __construct($service)
	{
		$this->init($service);
	}

	/**
	 * Initialize
	 *
	 * @param object $service Google Service (Get from GoogleAPI::getService())
	 * @return bool Result
	 */
	public function init($service)
	{
		self::$service = $service;

		return true;
	}

	/**
	 * Event List 
	 *
	 * @param string $calendarId
	 * @param array $optParams
	 * @return array Calendar data
	 */
	public static function eventList($calendarId='primary', $optParams=NULL)
	{
		try {
			
			# Option params
			$optParams = $optParams ? $optParams : [
												'maxResults' => 10,
												'orderBy' => 'startTime',
												'singleEvents' => TRUE,
												'timeMin' => date('c'),
												];

			# List by service
			$results = self::getService()->events->listEvents($calendarId, $optParams);

			# Output data
			$dataArray = [];

			# Data format process
			if (count($results->getItems()) > 0) {

			    foreach ($results->getItems() as $event) {

			        $data['title'] = $event->getSummary();
			        $data['start'] = $event->start->dateTime ? $event->start->dateTime : $event->start->date;
			        $data['end'] = $event->end->dateTime ? $event->end->dateTime : $event->end->date;
			        $dataArray[] = $data;
			    }
			}

		} catch (Exception $e) {
			
			$errorArray = json_decode($e->getMessage(), true);
			// print_r($errorArray);exit;

			$errorArray = $errorArray['error'];

			throw new Exception($errorArray['message'], $errorArray['code']);
		}

		return $dataArray;
	}

	/**
	 * Event Insert
	 *
	 * @param array $optParams 
	 * @param string $calendarId Defaults to primary
	 * @param bool $returnHtml Return ID if false. Defaults to true
	 * @return array Calendar data
	 */
	public static function eventInsert($optParams=[], $calendarId='primary', $returnHtml=false)
	{
		$event = new Google_Service_Calendar_Event($optParams);

		$event = self::getService()->events->insert($calendarId, $event);

		return ($returnHtml) ? $event->htmlLink : $event->id;
	}

	/**
	 * Event Get
	 *
	 * @param string $eventID
	 * @param string $calendarId Defaults to primary
	 * @return object Google Calendar Event
	 */
	public static function eventGet($eventID, $calendarId='primary')
	{
		return self::getService()->events->get($calendarId, $eventID);
	}

	/**
	 * Event Update
	 *
	 * @param string $calendarId Defaults to primary
	 * @param array $optParams 
	 * @param bool $returnHtml Return ID if false. Defaults to true
	 * @return array Calendar data
	 */
	public static function eventUpdate($event, $calendarId='primary')
	{
		$updatedEvent = self::getService()->events->update($calendarId, $event->getId(), $event);

		return $updatedEvent->getUpdated();
	}

	/**
	 * Event Delete
	 *
	 * @param string $eventID
	 * @param string $calendarId Defaults to primary
	 * @return bool Result
	 */
	public static function eventDelete($eventID, $calendarId='primary')
	{
		try {

			self::getService()->events->delete($calendarId, $eventID);
			
		} catch (Exception $e) {
			
			$errorArray = json_decode($e->getMessage(), true);
			// print_r($errorArray);exit;

			$errorArray = $errorArray['error'];

			throw new Exception($errorArray['message'], $errorArray['code']);
		}

		return true;
	}

	/**
	 * Service get interface
	 *
	 * @return object Google Service
	 */
	private static function getService()
	{
		if (!self::$service) {
			
			throw new Exception("Component is not initialized, Service is empty", 404);
		}

		return self::$service;
	}
}