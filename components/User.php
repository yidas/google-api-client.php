<?php

/**
* Google API User for credentials
*
* Handling Google API Access Token for save, get and delete with extensible
* Data Store Carrier.
*
* @author   Nick Tsai <myintaer@gmail.com>
* @version 	1.0.0
*/
class User
{
	/**
	 * Use files to save access token
	 *
	 * @var boolean
	 */
	public static $storage = 'file';

	/**
	 * Storage support list
	 *
	 * @var array
	 */
	public static $storages = [
		'file' => [
			'filePath' => __DIR__ . '/../files',
		],
		'session' => [
			'key' => 'google_access_token',
		],
	];

	/**
	 * Initialization
	 *
	 * @return object Self
	 */
	public static function init($options=[])
	{
		// Session environment check
		if (session_status() == PHP_SESSION_NONE) {
		    session_start();
		}

		$defaultOptions = [
			'storage' => self::$storage,
			'storageConfig' => self::$storages[self::$storage],
		];

		$options = array_merge($defaultOptions, $options);

		// Set config
		self::$storage = $options['storage'];
		self::$storages[self::$storage] = $options['storageConfig'];

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

		switch (self::$storage) {

			case 'file':
				file_put_contents(self::_filepath(), $jsonData);
				break;

			case 'session':
			default:
				$_SESSION[self::_getConfig('key')] = $jsonData;
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

		switch (self::$storage) {

			case 'file':

				if (!file_exists(self::_filepath())) {
					return false;
				}

				$jsonData = file_get_contents(self::_filepath());
				break;

			case 'session':
			default:
				$jsonData = isset($_SESSION[self::_getConfig('key')]) ? $_SESSION[self::_getConfig('key')] : NULL;
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

		switch (self::$storage) {

			case 'file':
				unlink(self::_filepath());
				break;

			case 'session':
			default:
				unset($_SESSION[self::_getConfig('key')]);
				break;
		}

		return true;
	}

	/**
	 * Get config of current storage
	 *
	 * @param string $key
	 * @return mixed Value
	 */
	protected function _getConfig($key)
	{
		return self::$storages[self::$storage][$key] ? self::$storages[self::$storage][$key] : null;
	}

	/**
	 * Get credential file by Session
	 *
	 * @return string Filepath
	 */
	protected function _filepath()
	{
		return self::_getConfig('filePath') . '/' . session_id() . '.json';
	}
}