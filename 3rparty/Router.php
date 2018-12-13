<?php
namespace HSPDev\HuaweiApi;
/*
* Depends on:
* HSPDev\HuaweiApi\CustomHttpClient
*/

/**
* This class handles login, sessions and such.
* and provides relevant methods for getting at the details.
* This has probably become quite a god object, but it's nice to use.
*/
class Router
{
	private $http = null; //Our custom HTTP provider.

	private $routerAddress = 'http://192.168.9.1'; //This is the one for the router I got.

	//These two we need to acquire through an API call.
	private $sessionInfo = '';
	private $tokenInfo = '';
	
	public static function get_network_type($type)
	{
		switch ($type) 
		{
			case '0':
				$result = 'No Service';
				break;
			case '1':
				$result = 'GSM';
				break;
			case '2':
				$result = 'GPRS (2.5G)';
				break;
			case '3':
				$result = 'EDGE (2.75G)';
				break;
			case '4':
				$result = 'WCDMA (3G)';
				break;
			case '5':
				$result = 'HSDPA (3G)';
				break;
			case '6':
				$result = 'HSUPA (3G)';
				break;
			case '7':
				$result = 'HSPA (3G)';
				break;
			case '8':
				$result = 'TD-SCDMA (3G)';
				break;
			case '9':
				$result = 'HSPA+ (4G)';
				break;
			case '10':
				$result = 'EV-DO rev. 0';
				break;
			case '11':
				$result = 'EV-DO rev. A';
				break;
			case '12':
				$result = 'EV-DO rev. B';
				break;
			case '13':
				$result = '1xRTT';
				break;
			case '14':
				$result = 'UMB';
				break;
			case '15':
				$result = '1xEVDV';
				break;
			case '16':
				$result = '3xRTT';
				break;
			case '17':
				$result = 'HSPA+ 64QAM';
				break;
			case '18':
				$result = 'HSPA+ MIMO';
				break;
			case '19':
				$result = 'LTE (4G)';
				break;
			case '41':
				$result = 'UMTS (3G)';
				break;
			case '44':
				$result = 'HSPA (3G)';
				break;
			case '45':
				$result = 'HSPA+ (3G)';
				break;
			case '46':
				$result = 'DC-HSPA+ (3G)';
				break;
			case '64':
				$result = 'HSPA (3G)';
				break;
			case '65':
				$result = 'HSPA+ (3G)';
				break;
			case '101':
				$result = 'LTE (4G)';
				break;
			default:
				$result = 'n/a';
				break;
		}
    
		return $result;
	}

	public static function get_network_typecnx($type)
	{
		switch ($type) 
		{
			case '0':
				$result = '2G';
				break;
			case '2':
				$result = '3G';
				break;
			case '5':
				$result = '3G+';
				break;
			case '7':
				$result = '4G';
				break;
			default:
				$result = 'n/a';
				break;
		}
    
		return $result;
	}

	/*
	def get_connection_status(status):
    result = 'n/a'
    if status == '2' or status == '3' or status == '5' or status == '8' or status == '20' or status == '21' or status == '23' or status == '27' or status == '28' or status == '29' or status == '30' or status == '31' or status == '32' or status == '33':
        result = 'Connection failed, the profile is invalid'
    elif status == '7' or status == '11' or status == '14' or status == '37':
        result = 'Network access not allowed'
    elif status == '12' or status == '13':
        result = 'Connection failed, roaming not allowed'
    elif status == '201':
        result = 'Connection failed, bandwidth exceeded'
    elif status == '900':
        result = 'Connecting'
    elif status == '901':
        result = 'Connected'
    elif status == '902':
        result = 'Disconnected'
    elif status == '903':
        result = 'Disconnecting'
    elif status == '904':
        result = 'Connection failed or disabled'
    return result*/

	public function __construct()
	{
		$this->http = new CustomHttpClient();
	}

	/**
	* Sets the router address.
	*/
	public function setAddress($address)
	{
		//Remove trailing slash if any.
		$address = rtrim($address, '/');

		//If not it starts with http, we assume HTTP and add it.
		if(strpos($address, 'http') !== 0)
		{
			$address = 'http://'.$address;
		}

		$this->routerAddress = $address;
	}

	/**
	* Most API responses are just simple XML, so to avoid repetition
	* this function will GET the route and return the object.
	* @return SimpleXMLElement
	*/
	public function generalizedGet($route)
	{
		//Makes sure we are ready for the next request.
		$this->prepare();

		$xml = $this->http->get($this->getUrl($route));
		$obj = new \SimpleXMLElement($xml);

		//Check for error message
		if(property_exists($obj, 'code'))
		{
			throw new \UnexpectedValueException('The API returned error code: '.$obj->code);
		}

		return $obj;
	}


	/**
	* Gets the current router status.
	* @return SimpleXMLElement
	*/
	public function getStatus()
	{
		return $this->generalizedGet('api/monitoring/status');
	}

	/**
	* Gets traffic statistics (numbers are in bytes)
	* @return SimpleXMLElement
	*/
	public function getTrafficStats()
	{
		return $this->generalizedGet('api/monitoring/traffic-statistics');
	}

	/**
	* Gets monthly statistics (numbers are in bytes)
	* This probably only works if you have setup a limit.
	* @return SimpleXMLElement
	*/
	public function getMonthStats()
	{
		return $this->generalizedGet('api/monitoring/month_statistics');
	}
	
	public function getStartDate()
	{
		return $this->generalizedGet('api/monitoring/start_date');
	}

	/**
	* Info about the current mobile network. (PLMN info)
	* @return SimpleXMLElement
	*/
	public function getNetwork()
	{
		return $this->generalizedGet('api/net/current-plmn');
	}

	/**
	* Gets the current craddle status
	* @return SimpleXMLElement
	*/
	public function getCraddleStatus()
	{
		return $this->generalizedGet('api/cradle/status-info');
	}

	/**
	* Get current SMS count
	* @return SimpleXMLElement
	*/
	public function getSmsCount()
	{
		return $this->generalizedGet('api/sms/sms-count');
	}

	/**
	* Get current WLAN Clients
	* @return SimpleXMLElement
	*/
	public function getWlanClients()
	{
		return $this->generalizedGet('api/wlan/host-list');
	}

	/**
	* Get notifications on router
	* @return SimpleXMLElement
	*/
	public function getNotifications()
	{
		return $this->generalizedGet('api/monitoring/check-notifications');
	}

	/**
	* Reboot the routeur
	* @return boolean
	*/
	public function reboot()
	{
		//Makes sure we are ready for the next request.
		$this->prepare(); 

		$rebootXml = '<?xml version:"1.0" encoding="UTF-8"?><request><Control>1</Control></request>';
		$xml = $this->http->postXml($this->getUrl('api/device/control'), $rebootXml);
		$obj = new \SimpleXMLElement($xml);
		//Simple check if login is OK.
		return ((string)$obj == 'OK');
	}

	/**
	* Backup the routeur
	* @return boolean
	*/
	public function backup()
	{
		//Makes sure we are ready for the next request.
		$this->prepare(); 

		$backupXml = '<?xml version:"1.0" encoding="UTF-8"?><request><Control>3</Control></request>';
		$xml = $this->http->postXml($this->getUrl('api/device/control'), $backupXml);
		$obj = new \SimpleXMLElement($xml);
		//Simple check if login is OK.
		return ((string)$obj == 'OK');
	}

	/**
	* Shutdown the routeur
	* @return boolean
	*/
	public function shutdown()
	{
		//Makes sure we are ready for the next request.
		$this->prepare(); 

		$shutdownXml = '<?xml version:"1.0" encoding="UTF-8"?><request><Control>4</Control></request>';
		$xml = $this->http->postXml($this->getUrl('api/device/control'), $shutdownXml);
		$obj = new \SimpleXMLElement($xml);
		//Simple check if login is OK.
		return ((string)$obj == 'OK');
	}
	
	public function logout()
	{
		//Makes sure we are ready for the next request.
		$this->prepare(); 

		$logoutXml = '<?xml version:"1.0" encoding="UTF-8"?><request><Logout>1</Logout></request>';
		$xml = $this->http->postXml($this->getUrl('api/user/logout'), $logoutXml);
		$obj = new \SimpleXMLElement($xml);
		//Simple check if logout is OK.
		return ((string)$obj == 'OK');
	}

	/**
	* Gets the SMS inbox. 
	* Page parameter is NOT null indexed and starts at 1.
	* I don't know if there is an upper limit on $count. Your milage may vary.
	* unreadPrefered should give you unread messages first.
	* @return boolean
	*/
	public function setLedOn($on = false)
	{
		//Makes sure we are ready for the next request.
		$this->prepare(); 

		$ledXml = '<?xml version:"1.0" encoding="UTF-8"?><request><ledSwitch>'.($on ? '1' : '0').'</ledSwitch></request>';
		$xml = $this->http->postXml($this->getUrl('api/led/circle-switch'), $ledXml);
		$obj = new \SimpleXMLElement($xml);
		//Simple check if login is OK.
		return ((string)$obj == 'OK');
	}

	/**
	* Checks whatever we are logged in
	* @return boolean
	*/
	public function getLedStatus()
	{
		$obj = $this->generalizedGet('api/led/circle-switch');
		if(property_exists($obj, 'ledSwitch'))
		{
			if($obj->ledSwitch == '1')
			{
				return true;
			}
		}
		return false;
	}


	/**
	* Checks whatever we are logged in
	* @return boolean
	*/
	public function isLoggedIn()
	{
		$obj = $this->generalizedGet('api/user/state-login');
		if(property_exists($obj, 'State'))
		{
			/*
			* Logged out seems to be -1
			* Logged in seems to be 0.
			* What the hell?
			*/
			if($obj->State == '0')
			{
				return true;
			}
		}
		return false;
	}

	/**
	* Gets the SMS inbox. 
	* Page parameter is NOT null indexed and starts at 1.
	* I don't know if there is an upper limit on $count. Your milage may vary.
	* unreadPrefered should give you unread messages first.
	* @return SimpleXMLElement
	*/
	public function getInbox($page = 1, $count = 20, $unreadPreferred = false)
	{
		//Makes sure we are ready for the next request.
		$this->prepare(); 

		$inboxXml = '<?xml version="1.0" encoding="UTF-8"?><request>
			<PageIndex>'.$page.'</PageIndex>
			<ReadCount>'.$count.'</ReadCount>
			<BoxType>1</BoxType>
			<SortType>0</SortType>
			<Ascending>0</Ascending>
			<UnreadPreferred>'.($unreadPreferred ? '1' : '0').'</UnreadPreferred>
			</request>
		';
		$xml = $this->http->postXml($this->getUrl('api/sms/sms-list'), $inboxXml);
		$obj = new \SimpleXMLElement($xml);
		return $obj;
	}

	public function getOutbox($page = 1, $count = 20, $unreadPreferred = false)
	{
		//Makes sure we are ready for the next request.
		$this->prepare(); 

		$outboxXml = '<?xml version="1.0" encoding="UTF-8"?><request>
			<PageIndex>'.$page.'</PageIndex>
			<ReadCount>'.$count.'</ReadCount>
			<BoxType>2</BoxType>
			<SortType>0</SortType>
			<Ascending>0</Ascending>
			<UnreadPreferred>'.($unreadPreferred ? '1' : '0').'</UnreadPreferred>
			</request>
		';
		$xml = $this->http->postXml($this->getUrl('api/sms/sms-list'), $outboxXml);
		$obj = new \SimpleXMLElement($xml);
		return $obj;
	}
	
	public function setSmsRead($index)
	{
		//Makes sure we are ready for the next request.
		$this->prepare(); 

		$readXml = '<?xml version="1.0" encoding="UTF-8"?><request>
			<Index>'.$index.'</Index>
			</request>
		';
		$xml = $this->http->postXml($this->getUrl('api/sms/set-read'), $readXml);
		$obj = new \SimpleXMLElement($xml);
		//Simple check if login is OK.
		return ((string)$obj == 'OK');
	}

	/**
	* Deletes an SMS by ID, also called "Index".
	* The index on the Message object you get from getInbox
	* will contain an "Index" property with a value like "40000" and up.
	* Note: Will return true if the Index DOES NOT exist already.
	* @return boolean
	*/
	public function deleteSms($index)
	{
		//Makes sure we are ready for the next request.
		$this->prepare(); 

		$deleteXml = '<?xml version="1.0" encoding="UTF-8"?><request>
			<Index>'.$index.'</Index>
			</request>
		';
		$xml = $this->http->postXml($this->getUrl('api/sms/delete-sms'), $deleteXml);
		$obj = new \SimpleXMLElement($xml);
		//Simple check if login is OK.
		return ((string)$obj == 'OK');
	}

	/**
	* Sends SMS to specified receiver. I don't know if it works for foreign numbers, 
	* but for local numbers you can just specifiy the number like you would normally 
	* call it and it should work, here in Denmark "42952777" etc (mine).
	* Message parameter got the normal SMS restrictions you know and love.
	* @return boolean
	*/
	public function sendSms($receiver, $message)
	{
		//Makes sure we are ready for the next request.
		$this->prepare(); 

		/*
		* Note how it wants the length of the content also.
		* It ALSO wants the current date/time wtf? Oh well.. 
		*/
		$receiver_array = explode(';', $receiver);
		$sendSmsXml = '<?xml version="1.0" encoding="UTF-8"?><request>
			<Index>-1</Index>
			<Phones>';
		foreach ($receiver_array as $to)
			$sendSmsXml .= '<Phone>'.$to.'</Phone>';
		$sendSmsXml .= '</Phones>
			<Sca/>
			<Content>'.$message.'</Content>
			<Length>'.strlen($message).'</Length>
			<Reserved>1</Reserved>
			<Date>'.date('Y-m-d H:i:s').'</Date>
			<SendType>0</SendType>
			</request>
		';
		$xml = $this->http->postXml($this->getUrl('api/sms/send-sms'), $sendSmsXml);
		$obj = new \SimpleXMLElement($xml);
				
		//Simple check if login is OK.
		return ((string)$obj == 'OK');	
	}

	/**
	* Not all methods may work if you don't login.
	* Please note that the router is pretty aggressive 
	* at timing your session out. 
	* Call something periodically or just relogin on error.
	* @return boolean
	*/
	public function login($username, $password)
	{
		//Makes sure we are ready for the next request.
		$this->prepare(); 

		/*
		* Note how the router wants the password to be the following:
		* 1) Hashed by SHA256, then the raw output base64 encoded.
		* 2) The username is appended with the result of the above, 
		*	 AND the current token. Yes, the password changes everytime 
		*	 depending on what token we got. This really fucks with scrapers.
		* 3) The string from above (point 2) is then hashed by SHA256 again, 
		*    and the raw output is once again base64 encoded.
		* 
		* This is how the router login process works. So the password being sent 
		* changes everytime depending on the current user session/token. 
		* Not bad actually.
		*/
		$loginXml = '<?xml version="1.0" encoding="UTF-8"?><request>
		<Username>'.$username.'</Username>
		<password_type>4</password_type>
		<Password>'.base64_encode(hash('sha256', $username.base64_encode(hash('sha256', $password, false)).$this->http->getToken(), false)).'</Password>
		</request>
		';
		$xml = $this->http->postXml($this->getUrl('api/user/login'), $loginXml);
		$obj = new \SimpleXMLElement($xml);
		//Simple check if login is OK.
		return ((string)$obj == 'OK');
	}

	/**
	* Internal helper that lets us build the complete URL 
	* to a given route in the API
	* @return string
	*/
	private function getUrl($route)
	{
		return $this->routerAddress.'/'.$route;
	}

	/**
	* Makes sure that we are ready for API usage.
	*/
	private function prepare()
	{
		//Check to see if we have session / token.
		if(strlen($this->sessionInfo) == 0 || strlen($this->tokenInfo) == 0)
		{
			//We don't have any. Grab some.
			$xml = $this->http->get($this->getUrl('api/webserver/SesTokInfo'));
			$obj = new \SimpleXMLElement($xml);
			if(!property_exists($obj, 'SesInfo') || !property_exists($obj, 'TokInfo'))
			{
				throw new \RuntimeException('Malformed XML returned. Missing SesInfo or TokInfo nodes.');
			}
			//Set it for future use.
			$this->http->setSecurity($obj->SesInfo, $obj->TokInfo);
		}
	}
}
