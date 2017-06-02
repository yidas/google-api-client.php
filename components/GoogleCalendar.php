<?php

/**
* Google Calendar API Component
*
* @author   Nick Tsai <myintaer@gmail.com>
* @version 	1.1.2
* @see 		Composer: google/apiclient:^2.0
* @link 	https://developers.google.com/google-apps/calendar/v3/reference/
*/
class GoogleCalendar
{
	/**
	 * @var string Primary Calendar Title for calendarList()
	 */
	public static $primaryCalendarTitle = 'Primary';

	/**
	 * @var bool Show Primary Calendar Summary in Title
	 */
	public static $showPrimarySummary = true;

	/**
	 * @var Google Calendar Service Object
	 */
	private static $service;

	/**
	 * Alias of set()
	 */
	function __construct($params=[])
	{
		if ($params) {
			
			self::set($params);
		}
		
	}

	/**
	 * Setting
	 *
	 * @param array $params Configuration
	 *	'service' => object $service Google Service (Get from GoogleAPI::getService())
	 * 	'{prop}' => Each public properties
	 * @return object Self
	 */
	public static function set($params=[])
	{
		if (isset($params['service'])) {
			
			self::init($params['service']);
		}

		self::$primaryCalendarTitle = isset($params['primaryCalendarTitle']) 
			? $params['primaryCalendarTitle'] 
			: self::$primaryCalendarTitle;

		self::$showPrimarySummary = isset($params['showPrimarySummary']) 
			? $params['showPrimarySummary'] 
			: self::$showPrimarySummary;

		return new self;
	}

	/**
	 * Initialize Service
	 *
	 * @param object $service Google Service (Get from GoogleAPI::getService())
	 * @return object Self
	 */
	public static function init($service)
	{
		self::$service = $service;

		return new self;
	}

	/**
	 * Event List 
	 *
	 * @param bool $isOwner List only self's calendars
	 * @return array Calendar list data
	 */
	public static function calendarList($isOwner=false)
	{
		// Get Calendar.List
		$calendarItems = self::getService()->calendarList
			->listCalendarList()
			->getItems();

		// Process Calendar.List
		$calendarList = [];

		foreach ($calendarItems as $calendarListEntry) {

			// Detect owner calendar
			if ($isOwner && $calendarListEntry->getAccessRole()!='owner') {
				
				continue;
			} 

			// Detect Primary calendar and sort it to the first row
			if ($calendarListEntry->getPrimary()) {
				
				$calendarTitle = self::$primaryCalendarTitle; 

				$calendarTitle .= (self::$showPrimarySummary) ? ' ('.$calendarListEntry->getSummary().')' : '';

				$calendarList = array_merge(['primary'=>$calendarTitle], $calendarList);

				$myCalendarList = array_merge(['primary'=>$calendarTitle], $calendarList);

				continue;
			}

			$calendarID = $calendarListEntry->getID();

			$calendarList[$calendarID] = $calendarListEntry->getSummary();
		}
		
		return $calendarList;
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
			
			self::googleErrorHandle($e);
		}

		return $dataArray;
	}

	/**
	 * Event Insert
	 *
	 * @param array $optParams 
	 * @param string $calendarId Defaults to primary
	 * @param bool $returnHtml Return type: true=>htmllink, false=>eventID
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
	 * @param object Google Calendar Event
	 * @param string $calendarId Defaults to primary
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
			
			self::googleErrorHandle($e);
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

	/**
	 * Google Error Handler
	 *
	 * @param object $e Exception
	 */
	private static function googleErrorHandle($e)
	{
		$errorArray = json_decode($e->getMessage(), true);

		// Detect json or standard message caught by Google Error Handler
		if (json_last_error() != JSON_ERROR_NONE) {
			
			throw $e;
		}

		$errorArray = $errorArray['error'];

		throw new Exception($errorArray['message'], $errorArray['code']);	
	}
}