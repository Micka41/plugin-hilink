<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

require_once dirname(__FILE__) . '/../../3rparty/CustomHttpClient.php';
require_once dirname(__FILE__) . '/../../3rparty/Router.php';

date_default_timezone_set('Europe/Paris');
ini_set('default_charset','utf-8');

class hilink extends eqLogic {

	public static function deamon_info() {
		$return = array();
		$return['log'] = 'Hilink';	
		
		$adress = trim(config::byKey('huawei_adress','hilink'));
		$login = trim(config::byKey('huawei_login','hilink'));
		$password = trim(config::byKey('huawei_password','hilink'));
		
		//log::add('hilink', 'debug', 'Modem : ' . $adress . "; Login : " . $login . "; Mot de passe : " . $password);
		
		if ($adress != '' && $login != '' && $password != '')
		{
			//The router class is the main entry point for interaction.
			/*$router = new HSPDev\HuaweiApi\Router;
			$router->setAddress($adress);
			$result = $router->getStatus();
			
			if($result->ConnectionStatus != "")
			{
				$return['launchable'] = 'ok';
			}
			else
			{
				$return['launchable'] = 'nok';
				//$return['launchable_message'] = __('L\'adresse du modem est incorrect.', __FILE__);
			}*/
			$return['launchable'] = 'ok';
		}
		else
		{
			$return['launchable'] = 'nok';
			//$return['launchable_message'] = __('Adresse ou login ou mot de passe vide.', __FILE__);
		}
					
		$cron = cron::byClassAndFunction('hilink', 'refresh_message');
		if (is_object($cron) && $cron->running())
			$return['state'] = 'ok';
		else
			$return['state'] = 'nok';
		
		return $return;
		
		/*		
		$cron = cron::byClassAndFunction('hilink', 'RefreshInformation');
		if(is_object($cron) && $cron->running())
			$return['state'] = 'ok';
		else 
			$return['state'] = 'nok';
		return $return;*/
		
	}
	
	public static function deamon_start() {
		self::deamon_stop();
		
		log::add('hilink', 'debug', 'Démarrage du démon');
		
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') 
			return;
		if ($deamon_info['state'] == 'ok') 
			return;
			
		hilink::creation_infos();
		
		$cron = cron::byClassAndFunction('hilink', 'refresh_message');
		if (!is_object($cron)) {
			$cron = new cron();
			$cron->setClass('hilink');
			$cron->setFunction('refresh_message');
			$cron->setEnable(1);
			$cron->setDeamon(1);
			$cron->setSchedule('* * * * *');
			$cron->setTimeout('999999');
			$cron->save();
		}
		$cron->start();
		$cron->run();
	}
		
	public static function deamon_stop() 
	{
		log::add('hilink', 'debug', 'Arrêt du démon');
		
		$cron = cron::byClassAndFunction('hilink', 'refresh_message');
		if (is_object($cron)) {
			$cron->stop();
			$cron->remove();
		}
		/*$cache = cache::byKey('Freebox_OS::SessionToken');
		$cache->remove();*/
	}
		
	public static function dependancy_info() {
	}

	public static function dependancy_install() {
	}

  	public static function configuration() 
	{
		/*if (config::byKey('pin', 'gammu') == '' || config::byKey('nodeGateway', 'gammu') == '') {
	    log::add('gammu', 'error', 'Configuration plugin non remplie, impossible de configurer gammu');
	    die();
	} else {
	log::add('gammu', 'debug', 'Configuration gammu');
	}
    $install_path = dirname(__FILE__) . '/../../resources';
    $url = network::getNetworkAccess('internal') . '/plugins/gammu/core/api/jeeGammu.php?apikey=' . jeedom::getApiKey('gammu');

    $usbGateway = jeedom::getUsbMapping(config::byKey('nodeGateway', 'gammu'));
    $cmd = 'sudo /bin/bash ' . $install_path . '/install.sh ' . $install_path . ' ' . $usbGateway . ' ' . config::byKey('pin', 'gammu') . ' ' . $url;
    exec($cmd);
    log::add('gammu', 'debug', $cmd);

    $i = 1;
    foreach (eqLogic::byType('gammu', true) as $gammu) {
      $line = 'number' . $i . ' = ' . $gammu->getConfiguration('phone');
      $cmd = 'echo "' . $line . '" | sudo tee --append /etc/gammu-smsdrc';
      exec($cmd);
      $i++;
    }

    exec('sudo service gammu-smsd restart');*/
		self::deamon_start();
  	}
  
	public static function refresh_message()
	{
		$adress = trim(config::byKey('huawei_adress','hilink'));	
		log::add('hilink', 'debug', 'Modem : ' . $adress);
		
		//The router class is the main entry point for interaction.
		$router = new HSPDev\HuaweiApi\Router;
		
		//If specified without http or https, assumes http://
		$router->setAddress($adress);
		
		$pause = config::byKey('DemonSleep','hilink');
		log::add('hilink', 'debug', 'Pause : ' . $pause);
		if ($pause < 60)
			$nbpause = (int)(60 / $pause) + 1;
		else
			$nbpause = 1;
		
		$passage = $nbpause - 1;
		while(true)
		{	
			$passage++;
			hilink::check_message($router);
			
			/*$elogic = self::byLogicalId('modem', 'hilink');
			if (is_object($elogic)) {
				log::add('hilink', 'debug', 'coucou');
				foreach($elogic->getCmd('info') as $Commande){
					$Commande->execute();
				}
			}*/
			
			if ($nbpause == $passage)
			{
				hilink::refersh_info($router);
				$passage = 0;
			}
			
			sleep($pause);
		}
		log::add('hilink', 'debug', 'sortie de la boucle');
		self::deamon_stop();
	}
	
	public static function check_message($_router)
	{
		log::add('hilink', 'debug', 'Vérification des SMS');
		$router = $_router;
		$result = $router->getNotifications();
		$nbSMS = $result->UnreadMessage;
		if ($nbSMS != '0')
		{				
			log::add('hilink', 'debug', 'SMS non lu : ' . $nbSMS);
		
			$login = trim(config::byKey('huawei_login','hilink'));
			$password = trim(config::byKey('huawei_password','hilink'));
			
			log::add('hilink', 'debug', "Login : " . $login . "; Mot de passe : " . $password);
			
			//Username and password. 
			//Username is always admin as far as I can tell.
			if ($router->login($login, $password) == 'OK')
			{			
				$nb=0;
				$nbsmssupprime=0;
				$nbsmsinbox = trim(config::byKey('nbsmsinbox','hilink'));
				
				$page = 1;
				do 
				{
					$result = $router->getInbox($page);
					
					foreach($result->Messages->Message as $message)
					{
						$nb++;
						if ($message->Smstat == "0")
						{		
							$id = str_replace('+','',$message->Phone);						
							$elogic = self::byLogicalId($id, 'hilink');
							if (is_object($elogic)) {
								$messageSMS = trim($message->Content);
								log::add('hilink', 'debug', 'SMS reçu de ' . $message->Phone . ' : ' . $messageSMS);
						
								$router->setSmsRead($message->Index);
						
								//On affecte la commande
								$cmdlogic = hilinkCmd::byEqLogicIdAndLogicalId($elogic->getId(),'sms');
								if (is_object($cmdlogic))
								{
									$cmdlogic->setConfiguration('value', $messageSMS);
									$cmdlogic->save();
									$cmdlogic->event($messageSMS);
									log::add('hilink', 'debug', 'Envoi d\'un événement Jeedom');
								}
								
								$cmdlogicAsk = hilinkCmd::byEqLogicIdAndLogicalId($elogic->getId(),'send');
								if ($cmdlogicAsk->askResponse($messageSMS)) {
									continue (2);
								}
				
								if ($elogic->getConfiguration('interact') == 1) {							
									$parameters = array();
									$username = $elogic->getConfiguration('user','none');
									if ($username != 'none') {
										$user = user::byLogin($username);
										if (is_object($user)) {
											$parameters['profile'] = $username;
										}
									}
									log::add('hilink', 'debug', 'Utilisateur Jeedom : '. $username);

									$reply = interactQuery::tryToReply($messageSMS, $parameters);
									if(trim($reply['reply']) != "")	
									{		
										$router->sendSms($message->Phone, $reply['reply']);
										log::add('hilink', 'debug', 'SMS envoyé : ' . $message->Phone . ' : ' . $reply['reply']);
										hilink::cleanoutbox($router);
									}
								}
							}
							else
							{
								$teltransfert = trim(config::byKey('teltransfert','hilink'));
								if (teltransfert != "")
								{
									$messageSMS = trim($message->Content);
									log::add('hilink', 'debug', 'SMS reçu de ' . $message->Phone . ' : ' . $messageSMS);
									$transfer = "SMS de " . $message->Phone . " : " . $messageSMS;
									$router->sendSms($teltransfert, $transfer);
									log::add('hilink', 'debug', 'Transfert du SMS');
									
									$router->setSmsRead($message->Index);
								}
								else
								{
									log::add('hilink', 'warning', 'Perte du SMS, pas de numéro de transfert de renseigné');
								}
							}
						}
						
						if ($nb > $nbsmsinbox)
						{
							$router->deleteSms($message->Index);
							$nbsmssupprime++;
						}
					}
				
					if($nbsmssupprime == 0)
						$page++;
					
				} while ($result->Count == 20);
								
				if ($nbsmssupprime	> 0)
					log::add('hilink', 'info', $nbsmssupprime . ' message(s) supprimé(s) de la boîte de récéption.');
			
				$router->logout();
			}
			else
			{
				log::add('hilink', 'error', 'Erreur lors du login, vérifiez le login et mot de passe.');
			}
			
		}
	}	
	
	public static function AddEqLogic($Name, $_logicalId) 
	{
		$EqLogic = self::byLogicalId($_logicalId, 'hilink');
		if (!is_object($EqLogic)) {
			$EqLogic = new hilink();
			$EqLogic->setLogicalId($_logicalId);
			$EqLogic->setObject_id(null);
			$EqLogic->setEqType_name('hilink');
			$EqLogic->setIsEnable(1);
			$EqLogic->setIsVisible(1);
			$EqLogic->setConfiguration('typeobj', 'modem');
			$EqLogic->setName($Name);
			$EqLogic->save();
		}
		return $EqLogic;
	}
	
	public static function AddCommande($eqLogic, $_libelle, $_logicalId, $_order = '', $_type="info", $_subType='binary', $_visible = true, $Template='', $unite='', $_min = '', $_max = '') 
	{
		$Commande = $eqLogic->getCmd(null,$_logicalId);
		if (!is_object($Commande))
		{
			$VerifName=$_libelle;
			$Commande = new hilinkCmd();
			$Commande->setId(null);
			$Commande->setLogicalId($_logicalId);
			$Commande->setEqLogic_id($eqLogic->getId());
			$count=0;
			while (is_object(cmd::byEqLogicIdCmdName($eqLogic->getId(),$VerifName)))
			{
				$count++;
				$VerifName=$_libelle.'('.$count.')';
			}
			$Commande->setName($VerifName);
			$Commande->setSubType($_subType);
			$Commande->setUnite($unite);
			$Commande->setConfiguration('doNotRepeatEvent', 1);
			$Commande->setConfiguration('maxValue', $_max);
			$Commande->setConfiguration('minValue', $_min);
			$Commande->setDisplay("forceReturnLineAfter","1");
			$Commande->setIsVisible($_visible);
			if ($_order != '')
				$Commande->setOrder($_order);
				
			$Commande->setType($_type);

			$Commande->setTemplate('dashboard',$Template);
			$Commande->setTemplate('mobile', $Template);
			$Commande->save();
		}

		//return $Commande;
	}
	
	public static function creation_infos() 
	{
		$modem=self::AddEqLogic('Modem Huawei', 'modem');
		self::AddCommande($modem, 'Opérateur', 'operateur', 0, "info", 'string', true, 'line');
		self::AddCommande($modem, 'Type connection', 'typeconnection', 1, "info", 'string', true, 'line');
		self::AddCommande($modem, 'Signal', 'signal', 2, "info", 'numeric', true, '', '', 0, 5);
		self::AddCommande($modem, 'Date dernière réinitialisation', 'laststart', 3, "info", 'string', true, 'line');
		self::AddCommande($modem, 'Conso actuelle mensuelle', 'currentmonth', 4, "info", 'numeric');
		self::AddCommande($modem, 'Conso par jour', 'percentbyday', 5, "info", 'numeric', true, '', '%');
		
		self::AddCommande($modem, 'Conso max mensuelle', 'datalimit', '', "info", 'string', false, 'line');
		self::AddCommande($modem, 'Conso mensuelle down', 'currentmonthdownload', '', "info", 'numeric', false, 'line');
		self::AddCommande($modem, 'Conso mensuelle up', 'currentmonthupload', '', "info", 'numeric', false, 'line');
		
		self::AddCommande($modem, 'Redémarrer', 'reboot', '', "action", 'other', false);
		self::AddCommande($modem, 'Eteindre', 'shutdown', '', "action", 'other', false);
	}
    
	public static function GetUnit($_limit)
	{
		$value = '';
		if (trim($_limit) != '')
		{
			$unite = substr($_limit, -2, 2);
				
			switch ($unite) 
			{
				case 'GB':
					$value = 'Go';
					break;
				case 'MB':
					$value = 'Mo';
					break;
				case 'KB':
					$value = 'Ko';
					break;
				default:
					$value = 'o';
			}
		}

		return $value;
	}
	
	public static function GetLimitData($_limit)
	{
		if (trim($_limit) != '')
		{
			$unite = substr($_limit, -2, 2);
			$data = substr($_limit, 0, -2);
				
			switch ($unite) 
			{
				case 'GB':
					$data = bcmul($data, 1073741824);
					break;
				case 'MB':
					$data = bcmul($data, 1048576);
					break;
				case 'KB':
					$data = bcmul($data, 1024);
					break;
			}

			return $data;
		}
		else
		   return 0;
	}	
	
	public static function ConvertData($_data, $_unit)
	{		
		$data = 0.00;
		switch ($_unit) 
		{
			case 'Go':
				$data = bcdiv($_data, 1073741824, 2);
				break;
			case 'Mo':
				$data = bcdiv($_data, 1048576, 2);
				break;
			case 'Ko':
				$data = bcdiv($_data, 1024, 2);
				break;
			default:
				$data = $_data;
				break;
		}

		return $data;
	}

	public static function PercentDailyUsed($_startdate, $_limit, $_download, $_upload)
	{		
		if ($_limit != 0)
		{
			$startdate = strtotime($_startdate);
			if ($startdate != 0)
			{
				$enddate = strtotime('+1 month', $startdate);

				// On récupère la différence de timestamp entre les 2 précédents
				$nbJoursMoisTimestamp = $enddate - $startdate;
				$nbJoursMois = $nbJoursMoisTimestamp / 86400;

				$date = new DateTime();
				$nbJoursNowTimestamp = $date->getTimestamp() - $startdate;
				$nbJoursNow = $nbJoursNowTimestamp / 86400; 

				$planbyday = bcdiv($_limit, $nbJoursMois, 4);

				$used = bcadd($_download, $_upload);

				$usedbyday = bcdiv($used, $nbJoursNow, 4);

				$percentbyday = bcdiv($usedbyday, $planbyday, 4) * 100;
				
				return $percentbyday;
			}
			else
				return 0;
		}
		else
			return 0;
	}
		
	public static function refersh_info($router) 
	{		
		$EqLogic = self::byLogicalId('modem', 'hilink');
		if (is_object($EqLogic)) 
		{		
			$status = $router->getStatus();
			$network = $router->getNetwork();
			$startdate = $router->getStartDate();
			$monthstats = $router->getMonthStats();
			
			$unit = hilink::GetUnit($startdate->DataLimit);
			
			$datalimit = hilink::GetLimitData($startdate->DataLimit);		

			$percentbyday = hilink::PercentDailyUsed($monthstats->MonthLastClearTime, $datalimit, $monthstats->CurrentMonthDownload, $monthstats->CurrentMonthUpload);
			
			foreach($EqLogic->getCmd('info') as $Commande)
			{				
				switch ($Commande->getLogicalId()) 
				{
					case "operateur":
						$value = $network->ShortName;
						break;
					case "typeconnection":
						$value	= HSPDev\HuaweiApi\Router::get_network_typecnx($network->Rat);	
						break;
					case "signal":
						$value = $status->SignalIcon;
						break;
					case "startday":
						$value = $startdate->StartDay;
						break;	
					case "laststart":
						$value = $monthstats->MonthLastClearTime;
						break;	
					case "datalimit":
						$value = hilink::ConvertData($datalimit, $unit);
						$Commande->setUnite($unit);
						break;						
					case "currentmonthdownload":
						$value = hilink::ConvertData($monthstats->CurrentMonthDownload, $unit);
						$Commande->setUnite($unit);
						break;						
					case "currentmonthupload":
						$value = hilink::ConvertData($monthstats->CurrentMonthUpload, $unit);
						$Commande->setUnite($unit);
						break;
					case "percentbyday":
						$value = $percentbyday;
						$Commande->setConfiguration('maxValue', max($value, 100));
						break;
					case "currentmonth";
						$value = hilink::ConvertData(bcadd($monthstats->CurrentMonthDownload, $monthstats->CurrentMonthUpload), $unit);
						$Commande->setUnite($unit);
						$Commande->setConfiguration('maxValue', max($value, hilink::ConvertData($datalimit, $unit)));
						log::add('hilink', 'debug', 'Max : ' . max($value, hilink::ConvertData($datalimit, $unit)));
						break;					
				}
				
				log::add('hilink', 'debug', $Commande->getLogicalId() . ' : ' . $value);

				if (isset($value) && $Commande->execCmd() != $value){
					//$EqLogic->checkAndUpdateCmd($Commande->getLogicalId(), $value);
					$Commande->setValue($value);
					$Commande->event($value, date('Y-m-d H:i:s'));
					$Commande->getEqLogic()->refreshWidget();
					$Commande->save();
					log::add('hilink', 'debug', 'Envoyé !');
				}
			}
		}
	}
     

	public function preUpdate() {
		if ($this->getConfiguration('typeobj') != 'modem')
		{
			if ($this->getConfiguration('phone') == '') {
				throw new Exception(__('Le numéro de téléphone ne peut être vide',__FILE__));
			}
		}
	}

	public function preSave() {
		if ($this->getConfiguration('typeobj') != 'modem')
		{
			$id = str_replace('+','',$this->getConfiguration('phone'));
			$this->setLogicalId($id);
		}
	}

	public function postUpdate() {
		hilink::configuration();
	}

	public function postSave()
	{
		log::add('hilink', 'debug', 'post save ' . $this->getLogicalId());

		if ($this->getLogicalId() != 'modem')
	  	{
  			$hlCmd = hilinkCmd::byEqLogicIdAndLogicalId($this->getId(),'sms');
      	  	if (!is_object($hlCmd)) {
        		$hlCmd = new hilinkCmd();
        		$hlCmd->setEqLogic_id($this->getId());
        		$hlCmd->setEqType('hilink');
        		$hlCmd->setIsHistorized(0);
				$hlCmd->setIsVisible(1);
        		$hlCmd->setName(__('Message', __FILE__));
        		$hlCmd->setLogicalId('sms');
      	  	}
      		$hlCmd->setConfiguration('data', 'sms');
      	  	$hlCmd->setType('info');
      		$hlCmd->setSubType('string');
      		$hlCmd->save();
	  
      	  	$hlCmd = hilinkCmd::byEqLogicIdAndLogicalId($this->getId(),'send');
      	  	if (!is_object($hlCmd)) {
        		$hlCmd = new hilinkCmd();
        		$hlCmd->setEqLogic_id($this->getId());
        		$hlCmd->setEqType('hilink');
        		$hlCmd->setIsHistorized(0);
				$hlCmd->setIsVisible(1);
       		 	$hlCmd->setName(__('Envoyer', __FILE__));
        		$hlCmd->setLogicalId('send');
      	  	}
      	  	$hlCmd->setConfiguration('data', 'send');
      	  	$hlCmd->setType('action');
      	  	$hlCmd->setSubType('message');
      	  	$hlCmd->save();
		}
		else
		{
			/*$hlCmd = hilinkCmd::byEqLogicIdAndLogicalId($this->getId(), 'reboot');
			if (!is_object($hlCmd)) {
				$hlCmd = new hilinkCmd();
				$hlCmd->setEqLogic_id($this->getId());
				$hlCmd->setName(__('Redémarrer', __FILE__));
				$hlCmd->setLogicalId('reboot');
			}
			$hlCmd->setType('action');
			$hlCmd->setSubType('other');
			$hlCmd->save();
			
			$hlCmd = hilinkCmd::byEqLogicIdAndLogicalId($this->getId(), 'shutdown');
			if (!is_object($hlCmd)) {
				$hlCmd = new hilinkCmd();
				$hlCmd->setEqLogic_id($this->getId());
				$hlCmd->setName(__('Eteindre', __FILE__));
				$hlCmd->setLogicalId('shutdown');
			}
			$hlCmd->setType('action');
			$hlCmd->setSubType('other');
			$hlCmd->save();	*/	
		}
  	}
  
	public static function cleanoutbox($_router)  
	{
		$nb=0;
		$nbsmssupprime=0;
		$nbsmsoutbox = trim(config::byKey('nbsmsoutbox','hilink'));
	
		$smscount = $_router->getSmsCount();
	
		log::add('hilink', 'debug', 'Nombre de SMS dans le dossier envoyé : ' . $smscount->LocalOutbox);
		
		if ($smscount->LocalOutbox > $nbsmsoutbox)
		{	
			$page = 1;
			do 
			{		
				$result = $_router->getOutbox($page);
				
				foreach($result->Messages->Message as $message)
				{
					$nb++;
					if ($nb > $nbsmsoutbox)
					{
						if ($_router->deleteSms($message->Index))
							$nbsmssupprime++;
					}
				}
		
				if ($nbsmssupprime == 0)
					$page++;
		
			} while ($result->Count == 20);
		}
	
		if ($nbsmssupprime	> 0)
			log::add('hilink', 'info', $nbsmssupprime . " message(s) supprimé(s) de la boîte d'envoi.");
	}
}

class hilinkCmd extends cmd {

  public function preSave() {
		if ($this->getSubtype() == 'message') {
			$this->setDisplay('title_disable', 1);
		}
	}

	public function execute($_options = array()) 
	{
		hilink::deamon_stop();
		
		$address = trim(config::byKey('huawei_adress','hilink'));
		$login = trim(config::byKey('huawei_login','hilink'));
		$password = trim(config::byKey('huawei_password','hilink'));
	
		log::add('hilink', 'debug', 'Modem : ' . $address . "; Login : " . $login . "; Mot de passe : " . $password);
		
		//The router class is the main entry point for interaction.
		$router = new HSPDev\HuaweiApi\Router;
	    //log::add('hilink', 'debug', 'Objet routeur créé');
		
		//If specified without http or https, assumes http://
		$router->setAddress($address);

		//Username and password. 
		//Username is always admin as far as I can tell.
		if ($router->login($login, $password) == 'OK') 
		{
			log::add('hilink', 'debug', 'Type ' . $this->getEqLogic()->getLogicalId());
			
			if ($this->getEqLogic()->getLogicalId() == 'modem')
			{
				switch ($this->getLogicalId()) 
				{					
					case 'reboot':
						if ($router->reboot() == 'OK') 
						{
							log::add('hilink', 'debug', 'Redémarrage du routeur envoyé.'); 
						}
						break;
					case 'shutdown':						
						if ($router->shutdown() == 'OK') 
						{
							log::add('hilink', 'debug', 'Arrêt du routeur envoyé.'); 
						}
						break;		
				}
			}
			else
			//téléphone
			{	
				log::add('hilink', 'debug', 'Cmd ' . $this->getLogicalId());
				switch ($this->getLogicalId()) 
				{			
					case 'sms':
						//Réception SMS
						log::add('hilink', 'debug', 'value ' . $this->getConfiguration('value'));	
						$this->setCollectDate(date('Y-m-d H:i:s'));
						$this->setConfiguration('doNotRepeatEvent', 1);
						$this->event($return);
						$this->getEqLogic()->refreshWidget();
					
						return $this->getConfiguration('value');	
						
						break;
						
					case 'send':
						//Envoi SMS
						$eqLogic = $this->getEqLogic();
						$phone = $eqLogic->getConfiguration('phone');
				
						if ($router->sendSms($phone, $_options['message']))
						{
							log::add('hilink', 'debug', 'SMS envoyé : ' . $phone . ' : ' . $_options['message']);
							hilink::cleanoutbox($router);
						}
						else
							log::add('hilink', 'error', 'Impossible d\'envoyer le SMS : ' . $_phones . ' : ' . $_message); 
							
						break;	
				}
			}

			$router->logout();
		}
		else
		{
			log::add('hilink', 'error', 'Erreur lors du login, vérifiez le login et le mot de passe.');
		}

		hilink::deamon_start();		
	}

}

?>
