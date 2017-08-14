<?php
/*Zoom Video Communications, Inc. 2015*/
/*Zoom Support*/
namespace Rignet\Zoom;

class ZoomAPI
{
	/**
	 * @const MEETING_TYPE_INSTANT instant meeting
	 */
	const MEETING_TYPE_INSTANT = 1;

	/**
	 * @const MEETING_TYPE_NORMAL normal scheduled meeting
	 */
	const MEETING_TYPE_NORMAL = 2;

	/**
	 * @const MEETING_TYPE_RECURRING recurring meeting without fixed time
	 */
	const MEETING_TYPE_RECURRING = 3;

	/**
	 * @const MEETING_TYPE_WEBINAR normal webinar
	 */
	const MEETING_TYPE_WEBINAR = 5;

	/**
	 * @const MEETING_TYPE_WEBINAR_RECURRING recurring webinar without fixed time
	 */
	const MEETING_TYPE_WEBINAR_RECURRING = 6;

	/**
	 * @const MEETING_TYPE_RECURRING_FIXED recurring meeting with fixed time
	 */
	const MEETING_TYPE_RECURRING_FIXED = 8;

	/**
	 * @const MEETING_TYPE_WEBINAR_RECURRING_FIXED recurring webinar without fixed time
	 */
	const MEETING_TYPE_WEBINAR_RECURRING_FIXED = 9;


	/*The API Key, Secret, & URL will be used in every function.*/
	private $api_key = 'Use setAPIKey to set your own API key';
	private $api_secret = 'Use setAPISecret to set your own API secret';
	private $api_url = 'https://api.zoom.us/v1/';

    /**
     * @var Rignet\Zoom\ZoomAPI
     */
    private static $instance;

	/**
	 * gets the instance via lazy initialization (created on first usage)
	 *
	 * @return Rignet\Zoom\ZoomAPI
	 */
	public static function getInstance()
	{
		if (null === static::$instance) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * is not allowed to call from outside to prevent from creating multiple instances,
	 * to use the singleton, you have to obtain the instance from ZoomAPI::getInstance() instead
	 */
	private function __construct()
	{
	}

	/**
	 * prevent the instance from being cloned (which would create a second instance of it)
	 */
	private function __clone()
	{
	}

	/**
	 * prevent from being unserialized (which would create a second instance of it)
	 */
	private function __wakeup()
	{
	}

	/**
	 * Set API key
	 *
	 * @param string $api_key API key (REQUIRED)
	 * @return string API key
	 */
	public function setAPIKey($api_key)
	{
		$this->api_key = $api_key;
		return $this->api_key;
	}
	
	/**
	 * Set API secret
	 *
	 * @param string $api_secret API secret (REQUIRED)
	 * @return string API secret
	 */
	public function setAPISecret($api_secret)
	{
		$this->api_secret = $api_secret;
		return $this->api_secret;
	}
	
	/*Function to send HTTP POST Requests*/
	/*Used by every function below to make HTTP POST call*/
	public function sendRequest($calledFunction, $data)
	{
		/*Creates the endpoint URL*/
		$request_url = $this->api_url.$calledFunction;

		/*Adds the Key, Secret, & Datatype to the passed array*/
		$data['api_key'] = $this->api_key;
		$data['api_secret'] = $this->api_secret;
		$data['data_type'] = 'JSON';

		$postFields = http_build_query($data);
		/*Check to see queried fields*/
		/*Used for troubleshooting/debugging*/
		if (defined('DEBUG') && DEBUG === true) {
	      echo $postFields;
	    }

		/*Preparing Query...*/
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_URL, $request_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		$response = curl_exec($ch);
		
		curl_close($ch);

		/*Will print back the response from the call*/
		/*Used for troubleshooting/debugging		*/
		if (defined('DEBUG') && DEBUG === true) {
	      echo $request_url;
		  var_dump($data);
		  var_dump($response);
	    }
		if(!$response) {
			return false;
		}
		/*Return the data in JSON format*/
		return json_decode($response);
	}
	/*Functions for management of users*/

	public function createAUser()
	{		
		$createAUserArray = array();
		$createAUserArray['email'] = $_POST['userEmail'];
		$createAUserArray['type'] = $_POST['userType'];
		return $this->sendRequest('user/create', $createAUserArray);
	}   

	public function autoCreateAUser()
	{
	  $autoCreateAUserArray = array();
	  $autoCreateAUserArray['email'] = $_POST['userEmail'];
	  $autoCreateAUserArray['type'] = $_POST['userType'];
	  $autoCreateAUserArray['password'] = $_POST['userPassword'];
	  return $this->sendRequest('user/autocreate', $autoCreateAUserArray);
	}

	public function custCreateAUser()
	{
	  $custCreateAUserArray = array();
	  $custCreateAUserArray['email'] = $_POST['userEmail'];
	  $custCreateAUserArray['type'] = $_POST['userType'];
	  return $this->sendRequest('user/custcreate', $custCreateAUserArray);
	}  

	public function deleteAUser()
	{
	  $deleteAUserArray = array();
	  $deleteAUserArray['id'] = $_POST['userId'];
	  return $this->sendRequest('user/delete', $deleteUserArray);
	}     

	public function listUsers()
	{
	  $listUsersArray = array();
	  return $this->sendRequest('user/list', $listUsersArray);
	}   

	public function listPendingUsers()
	{
	  $listPendingUsersArray = array();
	  return $this->sendRequest('user/pending', $listPendingUsersArray);
	}    

	/**
	 * Get user info.
	 *
	 * @param string[] $params Associative array of user query parameters:
	 *                         id: Zoom user ID
     */
	public function getUserInfo(Array $params = [])
	{
	  if (!isset($params['id'])) { return ['error' => 'Missing user id']; }

	  //$getUserInfoArray = array();
	  //$getUserInfoArray['id'] = $_POST['userId'];
	  //return $this->sendRequest('user/get',$getUserInfoArray);

	  return $this->sendRequest('user/get',$params);
	}   

	/**
	 * Get user info using email address.
	 *
	 * @param string[] $params Associative array of user query parameters:
	 *                         email:      email address associated with a Zoom user account
	 *                         login_type: (optional) Login type of the email, int
	 *                                          SNS_FACEBOOK = 0;
	 *                                          SNS_GOOGLE   = 1;
	 *                                          SNS_API      = 99;
	 *                                          SNS_ZOOM     = 100;
	 *                                          SNS_SSO      = 101;
     */
	public function getUserInfoByEmail(Array $params = [])
	{
	  if (!isset($params['email'])) { return ['error' => 'Missing user email address']; }
	  if (isset($params['login_type']) && empty($params['login_type'])) { unset($params['login_type']); }

	  //$getUserInfoByEmailArray = array();
	  //$getUserInfoByEmailArray['email'] = $_POST['userEmail'];
	  //$getUserInfoByEmailArray['login_type'] = $_POST['userLoginType'];
	  //return $this->sendRequest('user/getbyemail',$getUserInfoByEmailArray);

	  return $this->sendRequest('user/getbyemail',$params);
	}  

	public function updateUserInfo()
	{
	  $updateUserInfoArray = array();
	  $updateUserInfoArray['id'] = $_POST['userId'];
	  return $this->sendRequest('user/update',$updateUserInfoArray);
	}  

	public function updateUserPassword()
	{
	  $updateUserPasswordArray = array();
	  $updateUserPasswordArray['id'] = $_POST['userId'];
	  $updateUserPasswordArray['password'] = $_POST['userNewPassword'];
	  return $this->sendRequest('user/updatepassword', $updateUserPasswordArray);
	}      

	public function setUserAssistant()
	{
	  $setUserAssistantArray = array();
	  $setUserAssistantArray['id'] = $_POST['userId'];
	  $setUserAssistantArray['host_email'] = $_POST['userEmail'];
	  $setUserAssistantArray['assistant_email'] = $_POST['assistantEmail'];
	  return $this->sendRequest('user/assistant/set', $setUserAssistantArray);
	}     

	public function deleteUserAssistant()
	{
	  $deleteUserAssistantArray = array();
	  $deleteUserAssistantArray['id'] = $_POST['userId'];
	  $deleteUserAssistantArray['host_email'] = $_POST['userEmail'];
	  $deleteUserAssistantArray['assistant_email'] = $_POST['assistantEmail'];
	  return $this->sendRequest('user/assistant/delete',$deleteUserAssistantArray);
	}   

	public function revokeSSOToken()
	{
	  $revokeSSOTokenArray = array();
	  $revokeSSOTokenArray['id'] = $_POST['userId'];
	  $revokeSSOTokenArray['email'] = $_POST['userEmail'];
	  return $this->sendRequest('user/revoketoken', $revokeSSOTokenArray);
	}      

	public function deleteUserPermanently()
	{
	  $deleteUserPermanentlyArray = array();
	  $deleteUserPermanentlyArray['id'] = $_POST['userId'];
	  $deleteUserPermanentlyArray['email'] = $_POST['userEmail'];
	  return $this->sendRequest('user/permanentdelete', $deleteUserPermanentlyArray);
	}               

	/*Functions for management of meetings*/

	/**
	 * Create a meeting.
	 *
	 * @param string[] $params Associative array of meeting creation parameters:
	 *                         host_id:    Meeting host user ID
	 *                         topic:      Meeting topic. Max of 300 characters.
	 *                         type:       Meeting type:
	 *                                         1: means instant meeting (Only used for host to
	 *                                            start it as soon as created).
	 *                                         2: means normal scheduled meeting.
	 *                                         3: means a recurring meeting with no fixed time.
	 *                                         8: means a recurring meeting with fixed time.
	 *                                     Default: 2 // self::MEETING_TYPE_NORMAL
	 *                         start_time: (optional) Meeting start time in ISO datetime format.
	 *                                     For scheduled meeting and recurring meeting with fixed
	 *                                     time. Should be UTC time, such as 2012-11-25T12:00:00Z.
	 *                         password:   (optional) Meeting password. Password may only contain
	 *                                     the following characters: [a-z A-Z 0-9 @ - _ *].
	 *                                     Max of 10 characters.
     */
	public function createAMeeting(Array $params = ['type' => self::MEETING_TYPE_NORMAL])
	{
	  if (!isset($params['host_id'])) { return ['error' => 'Missing host_id']; }
	  if (!isset($params['topic'])) { return ['error' => 'Missing host_id']; }
	  if (!isset($params['start_time'])) {
	    /* Generate a meeting start time */
	    $d = new \DateTime('now',  new \DateTimeZone( 'UTC' ) );
	    $params['start_time'] = str_replace("'", '', var_export( $d->format('Y-m-d\TH:i:s\Z') , true) );
	  }
	  if (!isset($params['password'])) {
	    /* Generate a 8 to 10 character hex string for use as the meeting passwotrd. */
	    $params['password'] = dechex(rand(127, 255) * crc32( uniqid('', true) ));
	  }

	  //$createAMeetingArray = [];
	  //$createAMeetingArray['host_id'] = $_POST['userId'];
	  //$createAMeetingArray['topic'] = $_POST['meetingTopic'];
	  //$createAMeetingArray['type'] = $_POST['meetingType'];
	  //return $this->sendRequest('meeting/create', $createAMeetingArray);

	  return $this->sendRequest('meeting/create', $params);
	}

	public function deleteAMeeting()
	{
	  $deleteAMeetingArray = array();
	  $deleteAMeetingArray['id'] = $_POST['meetingId'];
	  $deleteAMeetingArray['host_id'] = $_POST['userId'];
	  return $this->sendRequest('meeting/delete', $deleteAMeetingArray);
	}

	/**
	 * List meetings of a particular user
	 *
	 * @param string[] $params Associative array of meeting query parameters:
	 *                         host_id:     Meeting host user ID
	 *                         page_size:   (optional) The amount of records returns within a single
	 *                                      API call. Defaults to 30. Max of 300 meetings.
	 *                                      Default: 30
	 *                         page_number: (optional) Current page number of returned records.
	 *                                      Default to 1.
	 *                                      Default: 1
     */
	public function listMeetings(Array $params = ['page_size' => 30, 'page_number' => 1])
	{
	  if (!isset($params['host_id'])) { return ['error' => 'Missing host_id']; }
	  if (isset($params['page_size']) && is_int($params['page_size']) && ($params['page_size'] > 300)) { $params['page_size'] = 300; }

	  //$listMeetingsArray = array();
	  //$listMeetingsArray['host_id'] = $_POST['userId'];
	  //return $this->sendRequest('meeting/list',$listMeetingsArray);

	  return $this->sendRequest('meeting/list',$params);
	}

	public function getMeetingInfo()
	{
      $getMeetingInfoArray = array();
	  $getMeetingInfoArray['id'] = $_POST['meetingId'];
	  $getMeetingInfoArray['host_id'] = $_POST['userId'];
	  return $this->sendRequest('meeting/get', $getMeetingInfoArray);
	}

	public function updateMeetingInfo()
	{
	  $updateMeetingInfoArray = array();
	  $updateMeetingInfoArray['id'] = $_POST['meetingId'];
	  $updateMeetingInfoArray['host_id'] = $_POST['userId'];
	  return $this->sendRequest('meeting/update', $updateMeetingInfoArray);
	}

	public function endAMeeting()
	{
      $endAMeetingArray = array();
	  $endAMeetingArray['id'] = $_POST['meetingId'];
	  $endAMeetingArray['host_id'] = $_POST['userId'];
	  return $this->sendRequest('meeting/end', $endAMeetingArray);
	}

	public function listRecording()
	{
      $listRecordingArray = array();
	  $listRecordingArray['host_id'] = $_POST['userId'];
	  return $this->sendRequest('recording/list', $listRecordingArray);
	}


	/*Functions for management of reports*/
	public function getDailyReport()
	{
	  $getDailyReportArray = array();
	  $getDailyReportArray['year'] = $_POST['year'];
	  $getDailyReportArray['month'] = $_POST['month'];
	  return $this->sendRequest('report/getdailyreport', $getDailyReportArray);
	}

	public function getAccountReport()
	{
	  $getAccountReportArray = array();
	  $getAccountReportArray['from'] = $_POST['from'];
	  $getAccountReportArray['to'] = $_POST['to'];
	  return $this->sendRequest('report/getaccountreport', $getAccountReportArray);
	}

	public function getUserReport()
	{
	  $getUserReportArray = array();
	  $getUserReportArray['user_id'] = $_POST['userId'];
	  $getUserReportArray['from'] = $_POST['from'];
	  $getUserReportArray['to'] = $_POST['to'];
	  return $this->sendRequest('report/getuserreport', $getUserReportArray);
	}


	/*Functions for management of webinars*/
	/**
	 * Create a webinar.
	 *
	 * @param string[] $params Associative array of webinar creation parameters:
	 *                         host_id:    Webinar host user ID
	 *                         topic:      Webinar topic. Max of 300 characters.
	 *                         agenda:     Webinar agenda.
	 *                         type:       Webinar type:
	 *                                         5: webinar,
	 *                                         6: recurrence webinar,
	 *                                         9: recurring webinar(With Fixed Time)
	 *                                     Default: 5
	 *                         start_time: (optional) Webinar start time in ISO datetime format.
	 *                                     For scheduled webinar and recurring webinar with fixed
	 *                                     time. Should be UTC time, such as 2012-11-25T12:00:00Z.
	 *                         password:   (optional) Webinar password. Password may only contain
	 *                                     the following characters: [a-z A-Z 0-9 @ - _ *].
	 *                                     Max of 10 characters.
     */
	public function createAWebinar(Array $params = [])
	{
	  if (!isset($params['host_id'])) { return ['error' => 'Missing host_id']; }
	  if (!isset($params['topic'])) { return ['error' => 'Missing host_id']; }
	  if (!isset($params['agenda'])) { $params['agenda'] = ''; }
	  if (!isset($params['type'])) { $params['type'] = self::webinar_TYPE_NORMAL; }
	  if (!isset($params['start_time'])) {
	    /* Generate a webinar start time */
	    $d = new \DateTime('now',  new \DateTimeZone( 'UTC' ) );
	    $params['start_time'] = str_replace("'", '', var_export( $d->format('Y-m-d\TH:i:s\Z') , true) );
	  }
	  if (!isset($params['password'])) {
	    /* Generate a 8 to 10 character hex string for use as the webinar passwotrd. */
	    $params['password'] = dechex(rand(127, 255) * crc32( uniqid('', true) ));
	  }

	  //$createAWebinarArray = [];
	  //$createAWebinarArray['host_id'] = $_POST['userId'];
	  //$createAWebinarArray['topic'] = $_POST['webinarTopic'];
	  //$createAWebinarArray['type'] = $_POST['webinarType'];
	  //return $this->sendRequest('webinar/create', $createAWebinarArray);
	  return $this->sendRequest('webinar/create', $params);
	}

	public function deleteAWebinar()
	{
	  $deleteAWebinarArray = array();
	  $deleteAWebinarArray['id'] = $_POST['webinarId'];
	  $deleteAWebinarArray['host_id'] = $_POST['userId'];
	  return $this->sendRequest('webinar/delete',$deleteAWebinarArray);
	}

	public function listWebinars()
	{
	  $listWebinarsArray = array();
	  $listWebinarsArray['host_id'] = $_POST['userId'];
	  return $this->sendRequest('webinar/list',$listWebinarsArray);
	}

	public function getWebinarInfo()
	{
	  $getWebinarInfoArray = array();
	  $getWebinarInfoArray['id'] = $_POST['webinarId'];
	  $getWebinarInfoArray['host_id'] = $_POST['userId'];
	  return $this->sendRequest('webinar/get',$getWebinarInfoArray);
	}

	public function updateWebinarInfo()
	{
	  $updateWebinarInfoArray = array();
	  $updateWebinarInfoArray['id'] = $_POST['webinarId'];
	  $updateWebinarInfoArray['host_id'] = $_POST['userId'];
	  return $this->sendRequest('webinar/update',$updateWebinarInfoArray);
	}

	public function endAWebinar()
	{
	  $endAWebinarArray = array();
	  $endAWebinarArray['id'] = $_POST['webinarId'];
	  $endAWebinarArray['host_id'] = $_POST['userId'];
	  return $this->sendRequest('webinar/end',$endAWebinarArray);
	}
}

