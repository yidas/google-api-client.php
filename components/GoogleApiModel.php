<?php

/**
* Google API Client Model
*
* Handling Google API Access Token for save, get and delete with extensible
* Data Store Carrier.
*
* @author   Nick Tsai <myintaer@gmail.com>
* @version 	1.0.0
* @see 		Composer: google/apiclient:^2.0
* @link 	https://developers.google.com/google-apps/calendar/v3/reference/
*/
class GoogleApiModel
{
	/**
	 * @var bool Save Access Token or not
	 */
	public static $saveToken = true;

	/**
	 * @var string Access Token store carrier
	 */
	public static $storeCarrier = 'session';

	/**
	 * @var string Session carrier: session key
	 */
	public static $carrierSessionKey = 'google_access_token';

	/**
	 * @var string File carrier: file path
	 */
	public static $carrierFilePath = __DIR__ . '/../files/credentials.json';

	/**
	 * Initialization
	 *
	 * @return object Self
	 */
	public static function init()
	{
		// Session environment check
		if (self::$storeCarrier=='session' && session_status() == PHP_SESSION_NONE) {
		    session_start();
		}

		return new self;
	}

	/**
	 * Token saving
	 *
	 * @param array OAuth Access Token
	 */
	public static function saveToken($accessTokenArray)
	{
		self::init();

		$jsonData = json_encode($accessTokenArray);

		switch (self::$storeCarrier) {

			case 'file':
				file_put_contents(self::$carrierFilePath, $jsonData);
				break;

			case 'session':
			default:
				$_SESSION[self::$carrierSessionKey] = $jsonData;
				break;
		}

		return true;
	}

	/**
	 * Token getting
	 *
	 * @return array OAuth Access Token
	 */
	public static function getToken()
	{
		self::init();

		switch (self::$storeCarrier) {

			case 'file':

				if (!file_exists(self::$carrierFilePath)) {
					return false;
				}

				$jsonData = file_get_contents(self::$carrierFilePath);
				break;

			case 'session':
			default:
				$jsonData = isset($_SESSION[self::$carrierSessionKey]) ? $_SESSION[self::$carrierSessionKey] : NULL;
				break;
		}

		return json_decode($jsonData, true);
	}

	/**
	 * Token deleting
	 */
	public static function deleteToken()
	{
		self::init();

		switch (self::$storeCarrier) {

			case 'file':
				file_put_contents(self::$carrierFilePath, NULL);
				break;

			case 'session':
			default:
				unset($_SESSION[self::$carrierSessionKey]);
				break;
		}

		return true;
	}
}