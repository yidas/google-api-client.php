<?php

namespace app\components;

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
	 * Storage data format
	 *
	 * @var array
	 */
	public static $data = [
		'services' => [],
		'registerService' => null,
		'accessToken' => null,
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

		self::$data = self::getData();

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

		return self::saveData('accessToken', json_encode($accessTokenArray));
	}

	/**
	 * Token getting
	 *
	 * @return array OAuth Access Token
	 */
	public static function getToken()
	{
		self::init();

		$accessToken = self::getData('accessToken');

		return ($accessToken) 
			? json_decode($accessToken, true) 
			: null;
	}

	/**
	 * Token deleting
	 */
	public static function deleteToken()
	{
		self::init();

		return self::saveData('accessToken', null);
	}

	/**
	 * Get Services
	 *
	 * @param stirng $service
	 * @return bool
	 */
	public static function getServices()
	{
		self::init();

		return self::getData('services');
	}

	/**
	 * Register Service
	 *
	 * @param stirng $service
	 * @return bool
	 */
	public static function registerService($service)
	{
		self::init();

		return self::saveData('registerService', $service);
	}

	/**
	 * Get Register Service
	 *
	 * @param stirng $service
	 * @return bool
	 */
	public static function getRegisterService()
	{
		self::init();

		return self::getData('registerService');
	}

	/**
	 * Add Service
	 *
	 * @param stirng $service
	 * @return bool
	 */
	public static function addService($service)
	{
		self::init();

		$services = self::getData('services');

		if (!in_array($service, $services)) {
			
			array_push($services, $service);
		}

		return self::saveData('services', $services);
	}

	/**
	 * Remove Service
	 *
	 * @param stirng $service
	 * @return bool
	 */
	public static function removeService($service)
	{
		self::init();

		$services = self::getData('services');

		if (($key = array_search($service, $services)) !== false) {
			
			unset($services[$key]);
		}

		return self::saveData('services', $services);
	}

	/**
	 * Save data
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	protected static function saveData($key, $value)
	{
		self::$data[$key] = $value;

		$streamData = json_encode(self::$data);

		switch (self::$storage) {

			case 'file':
				file_put_contents(self::_filepath(), $streamData);
				break;

			case 'session':
			default:
				$_SESSION[self::_getConfig('key')] = $streamData;
				break;
		}

		return true;
	}

	/**
	 * Get data
	 *
	 * @param string $key
	 * @return array self::$data
	 */
	protected static function getData($key=null)
	{
		switch (self::$storage) {

			case 'file':

				if (!file_exists(self::_filepath())) {

					$streamData = null;
					break;
				}

				$streamData = file_get_contents(self::_filepath());
				break;

			case 'session':
			default:
				$streamData = isset($_SESSION[self::_getConfig('key')]) ? $_SESSION[self::_getConfig('key')] : NULL;
				break;
		}

		if ($streamData) {
			
			self::$data = json_decode($streamData, true);
		}

		if ($key) {
			
			return isset(self::$data[$key]) ? self::$data[$key] : null;

		} else {

			return self::$data;
		}
	}

	/**
	 * Reset data
	 *
	 * @return void
	 */
	public static function resetData()
	{
		switch (self::$storage) {

			case 'file':

				if (!file_exists(self::_filepath())) {

					$streamData = null;
					break;
				}

				$streamData = unlink(self::_filepath());
				break;

			case 'session':
			default:
				session_destroy();
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