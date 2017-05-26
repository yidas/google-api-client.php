<?php

/**
* Google API Client Provider Component
*
* Provide interface and the cache of Client that help to develop Google API 
* OAuth with getting services.
*
* @author   Nick Tsai <myintaer@gmail.com>
* @version 	1.1.0
* @see 		Composer: google/apiclient:^2.0
* @link 	https://developers.google.com/google-apps/calendar/v3/reference/
* @example
* 	$googleClient = GoogleAPI::setClient([
*		'redirectUri' => 'http://' . $_SERVER['HTTP_HOST']
*		])
*		->setAccessToken(GoogleApiModel::getToken())
*		->getClient();
*	if (!GoogleAPI::isAuth()) {
*		$authUrl = GoogleAPI::createAuthUrl();
*	}
*	$servicePlus = GoogleAPI::getService('Google_Service_Plus');
*	$me = $servicePlus->people->get('me'); //echo $me['id'];
*
*   $moreScopesGoogleClient = GoogleAPI::addScopes([
*			Google_Service_Calendar::CALENDAR,
*			Google_Service_Drive::DRIVE,
*			])
*		->setClient()
*		->getClient();
*/
class GoogleAPI 
{
	
	/**
	 * @var object Google Client Object
	 */
	private static $client;

	/**
	 * @var array Google API OAuth access token
	 */
	private static $accessToken;

	/**
	 * @var Google Calendar Service Object
	 */
	private static $services = [];

	/**
	 * @var string Google Client API Name
	 */
	public static $clientAppName = 'Google API';

	/**
	 * @var array Google Client API Scopes
	 */
	public static $clientScopes = [
			Google_Service_Plus::USERINFO_PROFILE,
			Google_Service_Plus::USERINFO_EMAIL,
			// Google_Service_Calendar::CALENDAR,
			// Google_Service_Drive::DRIVE,
			];

	/**
	 * @var string Google API console json secret file path
	 */
	public static $clientSecretPath = __DIR__ . '/../files/client_secret.json';

	/**
	 * @var string Authorized redirect URI
	 */
	public static $clientRedirectUri;

	/**
	 * Set Google API Client
	 *
	 * @param array Configuration based on properties
	 * @return object Self
	 */
	public static function setClient($config=[])
	{
		/**
		 * Configuration
		 */
		self::$clientAppName = (isset($config['appName'])) 
			? $config['appName'] 
			: self::$clientAppName;
		self::$clientScopes = (isset($config['scopes'])) 
			? $config['scopes'] 
			: self::$clientScopes;
		self::$clientSecretPath = (isset($config['authConfig'])) 
			? $config['authConfig'] 
			: self::$clientSecretPath;
		self::$clientRedirectUri = (isset($config['redirectUri'])) 
			? $config['redirectUri'] 
			: self::$clientRedirectUri;

		// Check secret file path
		if (!file_exists(self::$clientSecretPath)) {

			throw new Exception("Google API Provider: AuthConfig file doesn't exist, please check the filepath: " . self::$clientSecretPath, 404);
		}

		/**
		 * Initialized Google API Client
		 */
		$client = new Google_Client();
		$client->setApplicationName(self::$clientAppName);
		$client->setScopes(self::$clientScopes);
		$client->setAuthConfig(self::$clientSecretPath);
		$client->setRedirectUri(self::$clientRedirectUri);
		$client->setAccessType('offline');
  		$client->setApprovalPrompt('force'); 	// For accessToken refresh

		self::$client = $client;

		return new self;
	}

	/**
	 * Add Client Scope
	 *
	 * @param string|array $scopes Google API Client Scopes
	 * @return object Self
	 */
	public static function addScopes($scopes)
	{
		self::$clientScopes = is_array($scopes)
			? array_merge(self::$clientScopes, $scopes)
			: array_push(self::$clientScopes, $scope);

		return new self;
	}

	/**
	 * Set access token to client 
	 *
	 * @param array $accessToken
	 * @return object Self
	 */
	public static function setAccessToken($accessToken)
	{
		if ($accessToken) {
			
			$client = self::getClient();

			// Set AccessToken (throw error if token is invalid)
			$client->setAccessToken($accessToken);
			
			// Refresh the token if it's expired.
			if ($client->isAccessTokenExpired()) {

				$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
			}

			// Save authorized Client
			self::$client = $client;

			// Save accessToken
			self::$accessToken = $accessToken;
		}

		return new self;
	}

	/**
	 * Get Google API Client
	 *
	 * @return object Self
	 */
	public static function getClient()
	{
		if (!self::$client) {

			// Set Client with default configuration if client is not set
		    self::setClient();
		}

		return self::$client;
	}

	/**
	 * Authorize and get access token
	 *
	 * @param string $authCode Google OAuth callback code
	 * @return object Self
	 */
	public static function authorize($authCode=NULL)
	{
		$authCode = !$authCode && isset($_GET['code']) ? $_GET['code'] : $authCode;

		// Exchange authorization code for an access token.
		self::$accessToken = self::getClient()->fetchAccessTokenWithAuthCode($authCode);
		
		return new self;
	}

	/**
	 * Authorize and get access token
	 *
	 * @param string Google OAuth callback code
	 * @return array AccessToken
	 */
	public static function getAccessToken($authCode=NULL)
	{
		if (!self::$accessToken) {
			
			self::authorize($authCode);
		}

		return self::$accessToken;
	}

	/**
	 * Check is authorized or not
	 *
	 * @return bool Result
	 */
	public static function isAuth()
	{
		return self::$accessToken ? true : false;
	}

	/**
	 * Create AuthUrl
	 *
	 * @return string Google Client->createAuthUrl
	 */
	public static function createAuthUrl()
	{
		return self::getClient()->createAuthUrl();
	}

	/**
	 * Get a Google Service by Class name
	 *
	 * @param string $serviceName Google Service class name
	 * @return object Google Service
	 */
	public static function getService($serviceName)
	{
		# Check cache
		if (isset(self::$services[$serviceName])) {
			
			return self::$services[$serviceName];
		}

		self::$services[$serviceName] = new $serviceName(self::getClient());

		return self::$services[$serviceName];
	}
}