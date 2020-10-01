<?php

				ini_set('post_max_size', '20M');
				ini_set('upload_max_filesize', '20M');

				require_once 'include/libs/Smarty.class.php';
				$GLOBALS['Net_IPv4_Netmask_Map'] = array(
													0 => "0.0.0.0",
													1 => "128.0.0.0",
													2 => "192.0.0.0",
													3 => "224.0.0.0",
													4 => "240.0.0.0",
													5 => "248.0.0.0",
													6 => "252.0.0.0",
													7 => "254.0.0.0",
													8 => "255.0.0.0",
													9 => "255.128.0.0",
													10 => "255.192.0.0",
													11 => "255.224.0.0",
													12 => "255.240.0.0",
													13 => "255.248.0.0",
													14 => "255.252.0.0",
													15 => "255.254.0.0",
													16 => "255.255.0.0",
													17 => "255.255.128.0",
													18 => "255.255.192.0",
													19 => "255.255.224.0",
													20 => "255.255.240.0",
													21 => "255.255.248.0",
													22 => "255.255.252.0",
													23 => "255.255.254.0",
													24 => "255.255.255.0",
													25 => "255.255.255.128",
													26 => "255.255.255.192",
													27 => "255.255.255.224",
													28 => "255.255.255.240",
													29 => "255.255.255.248",
													30 => "255.255.255.252",
													31 => "255.255.255.254",
													32 => "255.255.255.255"
									);
				class webGUI {
								private $template;
								private $menu;
								private $mode;
								private $modeArray;
								private $channel;
								private $parentStr;
								private $data;
								private $templateName;
								private $navigation;
								private $debug;
								private $session;
								private $mainMenu;
								private $login;
								private $configKey;
								private $confdEnable;
								private $totalString;
								private $errorString;
								private $wlan0Mode;
								private $wlan1Mode;
								private $oldIpAddress;
								private $postedCountry;
								private $countryChanged;
                                private $clientModeSet;
								private $target_path;
								private $sessionFileName;
		public $config;
		private $maxClientsChanged;
		private $postedMaxClients0;
		private $postedMaxClients1;

		function __construct($template,$mode)
		{
			$this->template = $template;
			$this->confdEnable = true;
			$this->sessionFileName = '/tmp/sessionid';
			$this->errorString = '';
			$this->session = $this->checkSession();
			$this->mode = $mode;
			$this->totalString = '';
			$this->oldIpAddress = '';
			if (empty($this->mode))
				$this->mode = 'main';
			require_once('config.php');
			$this->config = $config;
		}

		function createSessionLink()
		{
			$fp = fopen($this->sessionFileName, 'w');
			fwrite($fp, session_id().','.$_SERVER['REMOTE_ADDR']);
			fclose($fp);
		}

		function checkSessionLink()
		{
			if(file_exists($this->sessionFileName)) {
				return true;
			}
			else {
				return false;
			}
		}

		function checkSameSession()
		{
			if($this->checkSessionLink()) {
				$sd = explode(',',file_get_contents($this->sessionFileName));
				if ($sd[0] == session_id()) {
					return true;
				}
				else {
					return false;
				}
			}
			else {
				return false;
			}
		}

		function removeSessionLink()
		{
			@unlink($this->sessionFileName);
			session_destroy();
		}

		function checkSessionExpired()
		{
			if ($this->checkSessionLink() && ((time()-@filemtime($this->sessionFileName))>300))
				return true;
			else
				return false;
		}

		function checkSession($debug=false)
		{
			$flag = false;

			session_cache_limiter('nocache');
			session_cache_expire(3);
			session_start();

			if($this->checkSessionExpired()) {
				$this->removeSessionLink();
			}

			if (isset($_POST['logout']) && $_POST['logout'] == "true") {
				if ($this->checkSameSession() || $this->checkSessionExpired()) {
					$this->removeSessionLink();
				}
				Header ("Location: index.php");
				return;
			}

			header( "Cache-Control: no-store, no-cache, must-revalidate, max-age=0" );
												header( "Pragma: no-cache" );

			if (!$this->checkSessionLink() && isset($_POST['login']) && $_POST['login']=='LOGIN') {
				if ($_POST['username']=='admin' && $this->checkPassword($_POST['password'])) {
					if(!$this->checkSessionLink()) {
						$_SESSION['username']='admin';
						$flag = true;
						$this->login='Login';
						$_SESSION['session_start']=time();
						$this->createSessionLink();
						Header ("Location: redirect.html");
					}
					return;
				}
				else {
					$this->errorString = "Invalid Username / Password!";
				}
			}
			else {
				if($this->checkSessionLink()) {
					if (!$this->checkSameSession()) {
					}
					else if ($this->checkSessionExpired()) {
						$this->removeSessionLink();
						header("Location: logout.html");  // Redirect to Login Page
						die;
					}
					else {
						$this->createSessionLink();
						$flag = true;
					}
				}
			}

			if ($debug) {
				print_r($_POST);
				print_r($_SESSION);
			}
			return $flag;
		}

    function getDefaultMode($band)
{
    $mode = '';
	if ($band != undefined) {
		if ($band == 'TWO') {
			$mode = ($this->config["MODE11G"]["status"])?(($this->config["MODE11N"]["status"])?'2':'1'):'0';
		}
		else if ($band == 'FIVE') {
			$mode = ($this->config["MODE11N"]["status"])?'4':'3';
		}
	}
	else {
		if ($this->config["TWOGHZ"]["status"]) {
			$mode = ($this->config["MODE11G"]["status"])?(($this->config["MODE11N"]["status"])?'2':'1'):'0';
		}
		else if (config.FIVEGHZ.status) {
			$mode = ($this->config["MODE11N"]["status"])?'4':'3';
		}
	}
	return $mode;
}

								function checkPassword($pass)
								{
												if ($this->confdEnable) {
																$dbPass = $this->getPassword();
												}
												else {
																$dbPass = $this->getDefaultPassword();
												}
												return ($pass == $dbPass);
								}

								function sessionEnabled()
								{
												return $this->session;
								}

								function getMenuData($level,$first='',$second='')
								{
												if ($level == 0) {
																$retArr	=	array(	"Login",	"Help");
												}
												else if ($level == 1) {
																$retArr	=	array('Configuration','Monitoring','Maintenance','Support');
												}
												else if ($level == 2) {
																if ($first == 'Configuration') {
																				$retArr	=	array(	"System",	"IP",	"Wireless",	"Security",	"Wireless Bridge");
																}
																else if ($first == 'Monitoring') {
																				$retArr	=	array(	"System",	"Wireless Stations",	"Rogue AP",	"Logs",	"Statistics", "Packet Capture");
																}
																else if ($first == 'Maintenance') {
																				$retArr	=	array(	"Password",	"Reset",	"Remote Management",	"Upgrade");
																}
																else if ($first == 'Support') {
																				$retArr	=	array(	"Documentation");
																}
																else if ($first == 'Help') {
																				$retArr	=	array(   "Online Documentation");
																	}
												}
												else if ($level == 3) {
																if ($first == 'Configuration') {
																				switch ($second) {
																								case "System":
							$retArr["Basic"]	=	array(	"General"	=>	"basicSettings",
																																																												"Time"	=>	"timeSettings");
							if ($this->config['MBSSID']["status"])
    							$retArr["Advanced"]["General"]	=	"basicSettings:wlanSettings";

							if ($this->config['HTTPREDIRECT']["status"])
								$retArr["Advanced"]["Hotspot"]	=	"dhcpsSettings:httpRedirectSettings:wlanSettings";

							if ($this->config['SYSLOGD']["status"])
								$retArr["Advanced"]["Syslog"]	=	"logSettings:wlanSettings";

							if ($this->config['ETHERNET_CONFIG']["status"])
								$retArr["Advanced"]["Ethernet"]	=	"ethernetSettings";
							break;
																								case "IP":
							$retArr["IP Settings"]	=	array(	"IP Settings"	=>	"basicSettings:monitor--ipAddress:monitor--subNetMask:monitor--defaultGateway:monitor--macAddress:monitor--primaryDNS:monitor--secondaryDNS");

							if ($this->config["DHCPSERVER"]["status"])
								$retArr["DHCP Server Settings"]	=	array(	"DHCP Server Settings"	=>	"dhcpsSettings:httpRedirectSettings:wlanSettings");

							if (($this->config["DHCP_SNOOPING"]["status"]) || ($this->config["IGMP_SNOOPING"]["status"]))
								$retArr["Snooping"]	=	array(	"Snooping"	=>	"basicSettings");

							break;
																								case "Wireless":
																												$retArr["Basic"]	=	array(	"Wireless Settings"	=>	"wlanSettings:vapSettings:staSettings:basicSettings", "Scheduled Wireless ON-OFF"  => 'basicSettings',
																																																																"QoS Settings"	=>	"wlanSettings:wmmSettings");
																												$retArr["Advanced"]	=	array(	"Wireless Settings"	=>	"wlanSettings",
																																																																				"QoS Settings"	=>	"wlanSettings:wmmSettings");
							break;
																								case "Security":
																												$retArr["Profile Settings"]	=	array(	"Profile Settings"	=>	"wlanSettings:vapSettings:info802dot1x:monitor--productId");
							if ($this->config["ROGUEAP"]["status"])
																													$retArr["Advanced"]["Rogue AP"]	=	"wlanSettings:monitor--apList--knownApTable--wlan0--configIterate:monitor--apList--knownApTable--wlan1--configIterate:monitor--apList--unknownApTable--wlan0--iterate:monitor--apList--unknownApTable--wlan1--iterate";

							if ($this->config["MACACL"]["status"])
								$retArr["Advanced"]["MAC Authentication"]	=	"wlanSettings:vapSettings:accessControlSettings:info802dot1x:r_monitor--stationList--newStaList--wlan0:r_monitor--stationList--newStaList--wlan1";

							$retArr["Advanced"]["Radius Server Settings"]	=	"info802dot1x:vapSettings:wlanSettings";
							break;
																								case "Wireless Bridge":
																												$retArr["Bridging"]	=	"wlanSettings:wdsSettings:monitor--macAddress--wlan0:monitor--macAddress--wlan1:staSettings";
							break;
																				}
					return $retArr;
																}
																else if ($first == 'Monitoring') {
																				switch ($second) {
																								case "System":
																												$retArr["System"]	=	"basicSettings:wlanSettings:monitor--sysVersion:monitor--ipAddress:monitor--subNetMask:monitor--defaultGateway:monitor--ethernetMacAddress:monitor--macAddress--wlan0:monitor--macAddress--wlan1:monitor--primaryDNS:monitor--secondaryDNS:monitor--currentChannel--wlan0:monitor--currentChannel--wlan1";
							break;
																								case "Wireless Stations":
																												$retArr["Wireless Stations"]	=	"wlanSettings:r_monitor--stationList--detectedStaList--wlan0:r_monitor--stationList--detectedStaList--wlan1";
							break;
																								case "Rogue AP":
																												$retArr	=	array(	"Unknown AP List"	=>	"wlanSettings:monitor--apList--unknownApTable--wlan0--iterate:monitor--apList--unknownApTable--wlan1--iterate",
																																												"Known AP List"	=>	"wlanSettings:monitor--apList--knownApTable--wlan0--iterate:monitor--apList--knownApTable--wlan1--iterate:"
																																												);
							break;
																								case "Logs":
																												$retArr["Logs"]	=	"logs:wlanSettings";
							break;
																								case "Statistics":
																												$retArr["Statistics"]	=	"wlanSettings:monitor--stats--lan:monitor--stats--wlan0:monitor--stats--wlan1:monitor--radioApStatus--wlan0:monitor--radioApStatus--wlan1";
                                                                                                case "Packet Capture":
                                                                                                                $retArr["Packet Capture"] = "wlanSettings:monitor--pktCaptureStatus";
							break;
																				}
																}
																elseif ($first == 'Maintenance') {
																				switch ($second) {
																								case "Password":
																												$retArr	=	array(	"Change Password"	=>	"basicSettings");
							break;
																								case "Reset":
																												$retArr	=	array(	"Reboot AP"	=>	"maintenance",
																																												"Restore Defaults"	=>	"maintenance");
							break;
																								case "Remote Management":
																												$retArr	=	array(	"SNMP"	=>	"remoteSettings:tr069CpeConfiguration",
																																												"Remote Console"	=>	"remoteSettings",
																																												"TR 069"	=>	"tr069CpeConfiguration:remoteSettings");
							break;
																								case "Upgrade":
																												$retArr	=	array(	"Firmware Upgrade"	=>	"maintenance",
                                                                                                                                                                                                                                                                                                                                                                "Firmware Upgrade TFTP"	=>	"maintenance",
																																												"Backup Settings"	=>	"maintenance",
																																												"Restore Settings"	=>	"maintenance");
							break;
																				}
																}
																elseif ($first == 'Support' || $first == 'Help') {
																				switch ($second) {
																								case "Documentation":
																												$retArr	=	array(	"Documentation"	=>	"Documentation:monitor--productId");
							break;
																								case "Online Documentation":
																												$retArr	=	array(	"Documentation"	=>	"Documentation:monitor--productId");
							break;
																				}
																}
												}
			return $retArr;
								}

								function setNavigation($debug=false)
								{
												if (isset($_POST['login']) && $_POST['login']=='LOGIN' && $this->session) {
																Header("Location: redirect.html");
												}
												if ($this->session && $this->mode != 'Login') {

																if (!empty($_REQUEST['menu1'])) {
																				$this->navigation[1]=$_REQUEST['menu1'];
																}
																else {
																				$this->navigation[1]='Configuration';
																}

																$this->mainMenu = $this->getMenuData(1);
																$this->secondMenu = $this->getMenuData(2, $this->navigation[1]);
																if (!empty($_REQUEST['menu2']))  {
																				$this->navigation[2]=$_REQUEST['menu2'];
																}
																else {
																				$this->navigation[2]=$this->secondMenu[0];
																}

																$this->thirdMenu = $this->getMenuData(3,$this->navigation[1],$this->navigation[2]);
																if (!empty($_REQUEST['menu3'])) {
																				$this->navigation[3]=$_REQUEST['menu3'];
																				$this->navigation[0]=$this->navigation[3];
																}
																else {
																				$this->navigation[3]=key($this->thirdMenu);
																				$this->navigation[0]=$this->navigation[3];
																}

																if (!empty($_REQUEST['menu4'])) {
																				$this->navigation[4]=$_REQUEST['menu4'];
																				$this->navigation[0]=$this->navigation[4];
																}
																else {
																				if (is_array($this->thirdMenu[$this->navigation[3]])) {
																								$this->navigation[4]=key($this->thirdMenu[$this->navigation[3]]);
																				}
																				else {
																								$this->navigation[4]=$this->navigation[3];
																				}
																				$this->navigation[0]=$this->navigation[4];
																}

				if (is_array($this->thirdMenu[$this->navigation[3]])) {
																				$this->configKey = $this->thirdMenu[$this->navigation[3]][$this->navigation[4]];
																}
																else {
																				$this->configKey = $this->thirdMenu[$this->navigation[3]];
																}

																if ($this->navigation[0] == 'Basic Settings')
																				$this->navigation[0]='General';

																if ($this->navigation[2] == "Security" || $this->navigation[2] == "Wireless Bridge") {
																				if (isset($_REQUEST['mode7']) && $_REQUEST['mode7'] != '') {
																								$this->navigation[7]=$_REQUEST['mode7'];
																								$this->navigation[0]="Edit Security Profile";
																				}
																				if (isset($_REQUEST['mode8'])!==false && $_REQUEST['mode8'] != '') {
																								$this->navigation[8]=$_REQUEST['mode8'];
																				}
																				if (isset($_REQUEST['mode9']) && $_REQUEST['mode9'] != '') {
																								$this->navigation[9]=$_REQUEST['mode9'];
																				}
					if (!$this->config["MBSSID"]["status"] && $this->navigation[3]=='Profile Settings') {
																								$this->navigation[0]="Edit Security Profile";
																								$this->navigation[7]='0';
																								$this->navigation[8]='0';
																				}
																}
												}
												else {
																$this->mainMenu=$this->getMenuData(0);
																if (!empty($_REQUEST['menu1'])) {
																				$this->navigation[1]=$_REQUEST['menu1'];
																				$this->navigation[0]=$_REQUEST['menu1'];
																}
																else {
																				$this->navigation[1]='Login';
																				$this->navigation[0]='Login';
																}
												}

				if (isset($this->navigation[7]) && $this->navigation[9]=='wdsprofile') {
																$this->configKey = "wdsSettings";
												}

												if ($debug) {
																print_r($this->navigation);
												}

												return $this->navigation;
								}

								/***************************************************
									* The following functions are for Getting the Data and Setting the data from the database (ConfigD/referenceDB)
									*
									* Modify these functions only if you need check with the backend....
									*
									*/

								function getData($debug=false)
								{
												if ($this->confdEnable) {
																$this->getConfDBData();
												}
												else {
																$this->getRefDBData();
												}
								}

								function getConfDBData()
								{
												$keyName = explode(':',$this->configKey);
												$str = '';
												foreach ($keyName as $val) {
																if (strpos($val,'monitor')!==false) {
																				$str .= $this->getMonitDData($val);
																}
																else {
																				$str .= $this->getConfigDData($val);
																}
												}
												$this->configKey = str_replace('r_','',$this->configKey);
												if (!empty($str)) {
																$this->getFormatData($str);
												}
								}

								function getRefDBData($debug=false)
								{
												$keyName = explode(':',$this->configKey);
												$str = '';
												$str .= $this->getRefDBDataFromFile('monitorFile.cfg');
												$str .= $this->getRefDBDataFromFile('configSave.cfg');

												$this->configKey = str_replace('r_','',$this->configKey);
												if (!empty($str)) {
																$this->getFormatData($str);
												}
								}

								function getRefDBDataFromFile($file)
								{
												if (!file_exists($file)) return '';
												$fp = fopen($file, "rb");
												if (!$fp) return '';
												$str = file_get_contents($file);
												fclose($fp);
												$str .= "\n";
												return $str;
								}

								function getConfigDData($val,$debug=false)
								{
												$str = '';
												$str .= conf_iterate("-rv","system:".$val);
												$str .= "\n";
												return $str;
								}

								function getMonitDData($val,$debug=false)
								{
												$str = '';
												$tempArray = explode('_',$val);
												if ($tempArray[0]=='r') {
																$tempStr = str_replace('--',':',str_replace('r_','',$val));
																$macList = conf_get("system:".$tempStr);
																if ($macList) {
                                                                    $macList = str_replace('\\ ',' ',$macList);
                                                                    $macList = explode(' ',str_replace('\\\\','\\',$macList));
                                                                    $macList = explode(';',$macList[1]);

                                                                    foreach ($macList as $eachMac) {
                                                                                    $fullKey = $tempStr.':'.$eachMac;
                                                                                    $str .= conf_get("system:".$fullKey);
                                                                                    $str .= "\n";
                                                                    }
                                                                }
												}
												else
												{
																$str .= conf_get("system:".str_replace('--',':',$val));
																$str .= "\n";
												}

												$str = str_replace('\\:',':',$str);
												$str = str_replace('\\ ',' ',$str);
												$str = str_replace('\\\\','\\',$str);
												$str = str_replace("\\\n","\n", $str);
												return $str;
								}


								function getPassword()
								{
												$passStr = conf_get("system:basicSettings:adminPasswd");
												$str = explode(' ',$passStr);
                                                $str[1] = str_replace('\:',':',$str[1]);
                                                //$str[1] = str_replace('\\ ',' ',$str[1]);
                                                //$str[1] = str_replace('\\\\','\\',$str[1]);

                                                return $str[1];
								}

								function getDefaultPassword()
								{
												if ($this->confdEnable) {
																$file="/etc/default-config";
												}
												else {
																$file="configSave.cfg";
												}
												if (!file_exists($file)) {
																$this->errorString = "Error reading default configuration file!";
												}
												else {
																$fp = fopen($file, "rb");
																if (!$fp) return '';
																$u = explode(" ",$line);

																while ($line = fscanf($fp,"%s %s",$u,$v)) {
																				if ($u== 'system:basicSettings:adminPasswd') {
																								$str=$v;
																				}
																}
																fclose($fp);
												}

												return $str;
								}

								function getFormatData($str ,$debug=false)
								{
			$str = str_replace('\\\\','\\',$str);
			$str = str_replace('\\:',':',$str);

												$contArray=explode("\n",$str);
												$tempStr=str_replace('--',':',$this->configKey);
												$dataList=explode(':',$tempStr);

												foreach($contArray as $line) {
																$line=trim($line);
																if (!empty($line)) {
																				$nameVal=explode(" ",$line);
																				$tmpArr=explode(":",$nameVal[0]);
					if (strpos('ssid',$nameVal[0])!==false || strpos('wepPassPhrase',$nameVal[0])!==false || strpos('wdsPresharedkey',$nameVal[0])!==false ||
						strpos('presharedKey',$nameVal[0])!==false || strpos('wdsWepPassPhrase',$nameVal[0])!==false)	 {
						str_replace('&nbsp;', ' ',$nameVal[1]);
						str_replace('&amp;', '&',$nameVal[1]);
					}
																				if ((strtoupper($tmpArr[count($tmpArr)-1]) == 'ITERATE') || (strtoupper($tmpArr[count($tmpArr)-1]) == 'CONFIGITERATE')) {
																								continue;
																				}
																				if (array_search($tmpArr[1],$dataList)!==false && $nameVal[1] != 't') {
																								$this->str2Array($tmpArr,$nameVal[1]);
																				}
																}
												}
												if ($this->config["TWOGHZ"]["status"] && !$this->config["DUAL_CONCURRENT"]["status"] && $this->data[wlanSettings][wlanSettingTable][wlan0][radioStatus] == '1') {
				$this->data['activeMode'] = $this->data[wlanSettings][wlanSettingTable][wlan0][operateMode];
												}
												if ($this->config["FIVEGHZ"]["status"] && !$this->config["DUAL_CONCURRENT"]["status"] && $this->data[wlanSettings][wlanSettingTable][wlan1][radioStatus] == '1') {
				$this->data['activeMode'] = $this->data[wlanSettings][wlanSettingTable][wlan1][operateMode];
												}
												$this->data['radioStatus0'] = $this->data[wlanSettings][wlanSettingTable][wlan0][radioStatus];
												$this->data['radioStatus1'] = $this->data[wlanSettings][wlanSettingTable][wlan1][radioStatus];
			//if ($this->config["DUAL_CONCURRENT"]["status"]) {
																if ($this->data['radioStatus0'] == '1')
								$this->data['activeMode0'] = $this->data[wlanSettings][wlanSettingTable][wlan0][operateMode];
																if ($this->data['radioStatus1'] == '1')
								$this->data['activeMode1'] = $this->data[wlanSettings][wlanSettingTable][wlan1][operateMode];
			//}

			if (!$this->config["DUAL_CONCURRENT"]["status"] && (empty($this->data['activeMode']) && $this->data['activeMode']!='0')) {
																if ((strpos('123'.$this->configKey,'wlanSettings')!== false) && !isset($this->navigation[7]) && ($this->navigation[2] != "Logs")){
																		$this->errorString = "Wireless Radio is turned off!";
                                                                }else if ((strpos('123'.$this->configKey,'wlanSettings')!== false) && (!$this->config["MBSSID"]["status"]) && ($this->navigation[0] == "Edit Security Profile") && ($this->data['radioStatus'] == '0')) {
                                                                        $this->errorString = "Wireless Radio is turned off!";
                                                                }
                                                    }
			else {
				if ($this->data['radioStatus0'] == '0' && $this->data['radioStatus1'] == '0' && $this->navigation[2] != "Logs")
					$this->errorString = "Wireless Radio is turned off!";
			}

												if ($this->config["TWOGHZ"]["status"] && is_array($this->data['vapSettings']['vapSettingTable']['wlan0'])) {
																ksort($this->data['vapSettings']['vapSettingTable']['wlan0']);
												}
												if ($this->config["FIVEGHZ"]["status"] && is_array($this->data['vapSettings']['vapSettingTable']['wlan1'])) {
																ksort($this->data['vapSettings']['vapSettingTable']['wlan1']);
												}
												if ($this->config["TWOGHZ"]["status"] && is_array($this->data['wdsSettings']['wdsSettingTable']['wlan0'])) {
																ksort($this->data['wdsSettings']['wdsSettingTable']['wlan0']);
												}
												if ($this->config["FIVEGHZ"]["status"] && is_array($this->data['wdsSettings']['wdsSettingTable']['wlan1'])) {
																ksort($this->data['wdsSettings']['wdsSettingTable']['wlan1']);
												}

												if ($debug) {
																print_r($this->data);
																print_r($this->parentStr);
												}
								}

								function str2Array($keyArr,$val)
								{
												if (count($keyArr) > 2) {

																for($k = 1 ; $k < count($keyArr); $k++) {
																				$par_str .= "['".$keyArr[$k]."']";
																}
																if ($this->navigation[7]!= '') {
																				if ($this->navigation[9] == 'wdsprofile') {
																								if ((array_search('wds'.$this->navigation[7],$keyArr) === false) && $keyArr[1] == 'wdsSettings') {
																												return false;
																								}
																								else if ((array_search($this->navigation[7],range(0, $this->config["NO_OF_WDS_VAPS"]["count"]))!==false) && (array_search('wlan'.$this->navigation[8],$keyArr)!==false) && ($keyArr[1] == 'wdsSettings')) {
																								//return only the current interface and current wds profile being selected ...
																								//echo '$this->data['.$keyArr[count($keyArr)-1].']=$val;'."\n";
																												eval('$this->data['.$keyArr[count($keyArr)-1].']=$val;');
																												eval('$this->parentStr['.$keyArr[count($keyArr)-1].']=$keyArr[0].$par_str;');
																								}
																				}
																				else {
																								if ((array_search('vap'.$this->navigation[7],$keyArr) === false) && $keyArr[1] == 'vapSettings') {
																												return false;
																								}
																								else if ((array_search($this->navigation[7],range(0, $this->config["NO_OF_VAPS"]["count"]))!==false) && (array_search('wlan'.$this->navigation[8],$keyArr)!==false) && ($keyArr[1] == 'vapSettings')) {
																								//return only the current interface and current vap profile being selected ...
																								//echo '$this->data['.$keyArr[count($keyArr)-1].']=$val;'."\n";
																												eval('$this->data['.$keyArr[count($keyArr)-1].']=$val;');
																												eval('$this->parentStr['.$keyArr[count($keyArr)-1].']=$keyArr[0].$par_str;');
																								}
																								else if (($keyArr[3] == 'priRadIpAddr')) {
																												eval('$this->data['.$keyArr[3].']=$val;');
																								}
                                                                                                else if($this->navigation[7] == '0' && $this->navigation[0] == "Edit Security Profile"){
                                                                                                            if ($this->confdEnable){
                                                                                                                $RadioStatus = conf_get("system:wlanSettings:wlanSettingTable:wlan0:radioStatus");
                                                                                                                $APMode = conf_get("system:wlanSettings:wlanSettingTable:wlan0:apMode");
                                                                                                            }
                                                                                                            else{
                                                                                                                $RadioStatus = "system:wlanSettings:wlanSettingTable:wlan0:radioStatus 0";
                                                                                                                $APMode = "system:wlanSettings:wlanSettingTable:wlan0:apMode 1";
                                                                                                            }

                                                                                                            $RadioStat = explode(' ',$RadioStatus);
                                                                                                            $this->data['radioStatus'] = $RadioStat[1];
                                                                                                            unset($RadioStatus);
                                                                                                            unset($RadioStat);

                                                                                                            $WDSMode = explode(' ',$APMode);
                                                                                                            $this->data['wdsMode'] = $WDSMode[1];
                                                                                                            unset($APMode);
                                                                                                            unset($WDSMode);
                                                                                                        }
                                                                                                        if($this->navigation[0] == "Edit Security Profile"){
                                                                                                            if ($this->confdEnable){
                                                                                                                $radAuth = conf_get("system:wlanSettings:wlanSettingTable:wlan0:accessControlMode");
                                                                                                            }
                                                                                                            else{
                                                                                                                $radAuth = "system:wlanSettings:wlanSettingTable:wlan0:accessControlMode 1";
                                                                                                            }
											                                                                                                                                                                                  $macACLStatus = explode(' ',$radAuth);
                                                                                                            $this->data['macACLStatus'] = $macACLStatus[1];
                                                                                                            unset($radAuth);
                                                                                                            unset($macACLStatus);


                                                                                                        }

																				}
																}
																else {
																				if ($keyArr[5] == 'knownApSsid') {
																								eval('$arrflg=is_array($this->data'.str_replace('[\'knownApSsid\']','',$par_str).');');
																								if (!$arrflg)
																												eval('unset($this->data'.str_replace('[\'knownApSsid\']','',$par_str).');');
																				}
																				else if ($keyArr[5] == 'knownApChannel') {
																								eval('$arrflg=is_array($this->data'.str_replace('[\'knownApChannel\']','',$par_str).');');
																								if (!$arrflg)
																												eval('unset($this->data'.str_replace('[\'knownApChannel\']','',$par_str).');');
																				}
																				eval('$this->data'.$par_str.'=$val;');
																				eval('$this->parentStr'.$par_str.'=$keyArr[0].$par_str;');
																}
												}
								}

								function makeDeleteString($key, $value)
								{
												if (is_array($value))
												{
																foreach($value as $sub_key => $sub_value) {
																				$this->totalString .= $this->makeDeleteString($key . ":" .$sub_key,$sub_value);
																}
												}
												else {
																$valArr = explode(',',$value);
																foreach ($valArr as $val) {
																				if (!empty($val))
																								$str .= $key . ":" . trim(str_replace(':','-',str_replace('[','',str_replace(']','',$val)))) . "\n";
																}
																return $str;
												}
								}

								function makeString($key,$value)
								{
												if (is_array($value))
												{
																foreach($value as $sub_key => $sub_value) {
																				$this->totalString .= $this->makeString($key . ":" .$sub_key,$sub_value);
																}
												}
												else {
																if ($value != '') {
																				if (strpos($key,'apList')!==false || strpos($key,'accessControlSettings')!==false) {
																								$valArr = explode(',',$value);
																								foreach ($valArr as $val) {
																												if (!empty($val))
																																$str .= $key . ":" . trim(str_replace(':','-',str_replace('[','',str_replace(']','',$val)))) . " 1\n";
																								}
																								return $str;
																				}
																				else {
																								if (strpos($key,'remoteMacAddress')!==false || strpos($key,'macCloneAddr')!==false) {
																												$value = str_replace(':','-',$value);
																								}
																								if ($this->config["TWOGHZ"]["status"] && strpos(str_replace('\\\'','',$key),'system:wlanSettings:wlanSettingTable:wlan0:operateMode')!==false) {
																												$this->wlan0Mode = $value;
																								}
																								if ($this->config["FIVEGHZ"]["status"] && strpos(str_replace('\\\'','',$key),'system:wlanSettings:wlanSettingTable:wlan1:operateMode')!==false) {
																												$this->wlan1Mode = $value;
																								}
																								if ($this->config["TWOGHZ"]["status"] && $this->config["MAXSTATION_FEATURE"]["status"] && strpos(str_replace('\\\'','',$key),'system:wlanSettings:wlanSettingTable:wlan0:maxWirelessClients')!==false) {
							$this->postedMaxClients0 = $value;
						}
																								if ($this->config["FIVEGHZ"]["status"] && $this->config["MAXSTATION_FEATURE"]["status"] && strpos(str_replace('\\\'','',$key),'system:wlanSettings:wlanSettingTable:wlan1:maxWirelessClients')!==false) {
							$this->postedMaxClients1 = $value;
						}
																								if (strpos(str_replace('\\\'','',$key),'system:basicSettings:sysCountryRegion')!==false) {
																												$this->postedCountry = $value;
																								}

																								if (strpos($key,'ssid')!==false || strpos($key,'wepPassPhrase')!==false || strpos($key,'wdsPresharedkey')!==false || strpos($key,'presharedKey')!==false
								|| strpos($key,'wdsWepPassPhrase')!==false || strpos($key,'vapProfileName')!==false || strpos($key,'wdsProfileName')!==false  || strpos($key,'priRadSharedSecret')!==false  || strpos($key,'sndRadSharedSecret')!==false  || strpos($key,'priAcntSharedSecret')!==false  || strpos($key,'sndAcntSharedSecret')!==false) {
							$value = str_replace('&','&amp;',trim($value));
							$value = str_replace(' ','&nbsp;',trim($value));
							$value = str_replace('<','&#060;',trim($value));
							$value = str_replace('>','&#062;',trim($value));
							$value = str_replace('"','&quot;',trim($value));							
							$value = str_replace('\'','&#39;',trim($value));
							$value = str_replace('%A0','&nbsp;',urlencode($value));
							$value = urldecode($value);
						}
																								return $key . " " . trim($value) . "\n";
																				}
																}
																else {
																				return $key."\n";
																}
												}
								}

								function setData($valList,$debug=false)
								{
												if ($this->confdEnable) {
																$whiteSpaces	=	array(' ','\&nbsp;','%2F','+');
																if (str_replace($whiteSpaces,'',$this->navigation[4]) == 'IPSettings') {
																				$this->oldIpAddress = str_replace('system:monitor:ipAddress ','',conf_get("system:monitor:ipAddress"));
																}
				if ($this->config["TWOGHZ"]["status"]) {
					$this->oldApMode0 = str_replace('system:wlanSettings:wlanSettingTable:wlan0:apMode ','',conf_get("system:wlanSettings:wlanSettingTable:wlan0:apMode"));
				}
				if ($this->config["FIVEGHZ"]["status"]) {
					$this->oldApMode1 = str_replace('system:wlanSettings:wlanSettingTable:wlan1:apMode ','',conf_get("system:wlanSettings:wlanSettingTable:wlan1:apMode"));
				}
																if (!empty($valList['delete'])) {
																				$this->makeDeleteString("system",$valList['delete']);
																				unset($valList['delete']);
																}

																if ($this->config["TWOGHZ"]["status"] && empty($valList[system][accessControlSettings][wlanAccessControlLocalTable][wlan0])) {
																				unset($valList[system][accessControlSettings][wlanAccessControlLocalTable][wlan0]);
																}
																if ($this->config["FIVEGHZ"]["status"] && empty($valList[system][accessControlSettings][wlanAccessControlLocalTable][wlan1])) {
																				unset($valList[system][accessControlSettings][wlanAccessControlLocalTable][wlan1]);
																}
																if (empty($valList[system][accessControlSettings][wlanAccessControlLocalTable])) {
																				unset($valList[system][accessControlSettings][wlanAccessControlLocalTable]);
																}

																if ($this->config["TWOGHZ"]["status"] && empty($valList[system][apList][knownApTable][wlan0])) {
																				unset($valList[system][apList][knownApTable][wlan0]);
																}
																if ($this->config["FIVEGHZ"]["status"] && empty($valList[system][apList][knownApTable][wlan1])) {
																				unset($valList[system][apList][knownApTable][wlan1]);
																}
																if (empty($valList[system][apList][knownApTable])) {
																				unset($valList[system][apList][knownApTable]);
																}

																$this->makeString("system",$valList['system']);

																if ($this->config["TWOGHZ"]["status"] && $this->config["MAXSTATION_FEATURE"]["status"] && $this->navigation[3]=='Advanced' && $this->navigation[4] == 'Wireless Settings') {
																				if (isset($valList["dbMaxWirelessClients0"]) && $this->postedMaxClients0 != $valList["dbMaxWirelessClients0"]) {
			if((isset($valList["dbApMode0"])) && ($valList["dbApMode0"] != '5')){
																								$this->maxClientsChanged = true;
			    }
																				}
																}

																if ($this->config["FIVEGHZ"]["status"] && $this->config["MAXSTATION_FEATURE"]["status"] && $this->navigation[3]=='Advanced' && $this->navigation[4] == 'Wireless Settings') {
																				if (isset($valList["dbMaxWirelessClients1"]) && $this->postedMaxClients1 != $valList["dbMaxWirelessClients1"]) {
																								$this->maxClientsChanged = true;
																				}
																}

																$this->totalString = str_replace('\\\'','',$this->totalString);
																if ($this->navigation[3]=='Basic') {
																				if ($this->navigation[4] == 'Wireless Settings') {
						if ($this->config["TWOGHZ"]["status"]) {
							$modeChanged = $this->checkChangeMode('0');
							if ($modeChanged == '2b') {
								$this->totalString .=	"system:wmmSettings:wmmApEdcaSettingTable:wlan0:3:wmmApEdcaMaxBurst 6016\n";
								$this->totalString .=	"system:wmmSettings:wmmApEdcaSettingTable:wlan0:4:wmmApEdcaMaxBurst 3264\n";
								$this->totalString .=	"system:wmmSettings:wmmStaEdcaSettingTable:wlan0:3:wmmStaEdcaTxopLimit 6016\n";
								$this->totalString .=	"system:wmmSettings:wmmStaEdcaSettingTable:wlan0:4:wmmStaEdcaTxopLimit 3264\n";
								if ($this->config["MODE11G"]["status"] && $this->config["MODE11G"]["SUPERG"]["status"]) {
									$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan0:supergMode 0\n";
								}
							}
							else if ($modeChanged == '4mb') {
								$this->totalString .=	"system:wmmSettings:wmmApEdcaSettingTable:wlan0:3:wmmApEdcaMaxBurst 3008\n";
								$this->totalString .=	"system:wmmSettings:wmmApEdcaSettingTable:wlan0:4:wmmApEdcaMaxBurst 1504\n";
								$this->totalString .=	"system:wmmSettings:wmmStaEdcaSettingTable:wlan0:3:wmmStaEdcaTxopLimit 3008\n";
								$this->totalString .=	"system:wmmSettings:wmmStaEdcaSettingTable:wlan0:4:wmmStaEdcaTxopLimit 1504\n";
							}
						}
																				}
																				else if ($this->navigation[4] == 'General') {
																								if ($this->checkChangeCountry() == 'true') {
																												$this->countryChanged = true;
																												if ($this->config["TWOGHZ"]["status"]) {
								if ($this->config["MODE11G"]["status"]) {
												if ($this->config["MODE11N"]["status"]) {
																$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan0:operateMode 2\n";
																																				}
																																				else {
																																								$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan0:operateMode 1\n";
																																				}
																																}
																																else {
																																				$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan0:operateMode 0\n";
																																}
								$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan0:radioStatus 1\n";
								$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan0:channel 0\n";
                                $this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan0:apMode 0\n";
								$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan0:dataRate 0\n";
								$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan0:preambleType 0\n";
								if($this->config["ARIES"]["status"]){
									if ($this->confdEnable) {
										$productIdArr = explode(' ', conf_get('system:monitor:productId'));
											$productId = $productIdArr[1];
									}
									else {
										$productId = "WNAP210";
									}
									if ($productId == 'WG102')
										$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan0:txPower 2\n";
									else
										$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan0:txPower 0\n";
								}
								else{
									$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan0:txPower 0\n";
								}
								if ($this->config["MODE11N"]["status"]) {
									$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan0:mcsRate 99\n";
									$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan0:guardInterval 0\n";
									$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan0:channelWidth 0\n";
								}
							}

																												if ($this->config["FIVEGHZ"]["status"]) {
																																if ($this->config["MODE11N"]["status"]) {
																																				$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan1:operateMode 4\n";
																																}
																																else {
																																				$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan1:operateMode 3\n";
                                                                                                                                }
                        if($this->config["DUAL_CONCURRENT"]["status"]){
                            if ($this->get5GHzSupport() == false) {
                                    $this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan1:radioStatus 0\n";
                                }
                        }
                        else {
                            $this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan1:radioStatus 0\n";
                        }
								$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan1:channel 0\n";
                                $this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan1:apMode 0\n";
								$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan1:dataRate 0\n";
								$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan1:txPower 0\n";
								if ($this->config["MODE11N"]["status"]) {
									$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan1:mcsRate 99\n";
									$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan1:guardInterval 0\n";
									if ($this->get40MHzSupportedCountry($this->postedCountry))
										$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan1:channelWidth 2\n";
									else
										$this->totalString .=	"system:wlanSettings:wlanSettingTable:wlan1:channelWidth 0\n";
								}
							}
																								}
																				}
																}
                                                                if($this->clientModeSet == "1"){
                                                                    $this->totalString .=	"system:basicSettings:spanTreeStatus 0\n";
                                                                }
																conf_set_buffer($this->totalString);
																if ($debug)
																				print_r($this->totalString);
																conf_save();
												}
			if ($this->navigation[4] == 'IP Settings') {
				if ($this->confdEnable)
					$currentIpAddress = conf_get("system:monitor:ipAddress");
				$currentIpAddress = str_replace('system:monitor:ipAddress ','',$currentIpAddress);
				if ($this->oldIpAddress!='') {
					if ($currentIpAddress!= $this->oldIpAddress) {
						$this->removeSessionLink();
						include_once('redirect.php');
					}
					else if ($this->getDhcpClientStatus()) {
						$this->errorString = "No Response or Same IP assignment from DHCP Server";
					}
				}
			}
			if ($this->config["CLIENT"]["status"]) {
				if ($this->confdEnable) {
					if ($this->config["TWOGHZ"]["status"]) {
						$this->newApMode0 = str_replace('system:wlanSettings:wlanSettingTable:wlan0:apMode ','',conf_get("system:wlanSettings:wlanSettingTable:wlan0:apMode"));
					}
					if ($this->config["FIVEGHZ"]["status"]) {
						$this->newApMode1 = str_replace('system:wlanSettings:wlanSettingTable:wlan1:apMode ','',conf_get("system:wlanSettings:wlanSettingTable:wlan1:apMode"));
					}
				}
				else {
					$this->oldApMode = '5'; $this->newApMode = '5';
				}
			}

												//return $this->data;
								}

								function checkChangeMode()
								{
												if ($this->confdEnable)
																$Mode = conf_get("system:wlanSettings:wlanSettingTable:wlan0:operateMode");
												else
																$Mode = "system:wlanSettings:wlanSettingTable:wlan0:operateMode 0";
												$currentMode = explode(' ',$Mode);
												unset($Mode);
												if ($this->wlan0Mode == '0' && $currentMode[1] != '0') {
																return '2b';
												}
												else if ($this->wlan0Mode != '0' && $currentMode[1] == '0') {
																return '4mb';
												}
								}

								function checkChangeCountry()
								{
												if ($this->confdEnable)
																$Mode = conf_get("system:basicSettings:sysCountryRegion");
												else
																$Mode = "system:basicSettings:sysCountryRegion 840";
												$currentMode = explode(' ',$Mode);
												unset($Mode);

												if (($this->postedCountry != $currentMode[1]) && $this->postedCountry != '' && $currentMode[1] != '')  {
																return 'true';
												}
												else {
																return 'false';
												}
								}

		function returnAPMac()
		{
			if ($this->confdEnable) {
				$macAddress = explode(' ',conf_get("system:monitor:macAddress"));
				return $macAddress[1];
			}
			else {
				return "00:a4:92:c8:9f:34";
			}
		}
								/********************************************
									* End of database functions....
									*/

								function setTemplateName($debug=false)
								{
												if ($this->session && $this->mode != 'Login') {
																$whiteSpaces	=	array(' ','\&nbsp;','%2F','+');
																	if (isset($this->navigation[7]) && $this->navigation != '') {
																					if ($this->navigation[9] != '') {
																									$this->templateName = "wdsSecurityProfile.tpl";
																					}
																					else {
																									$this->templateName = "vapSecurityProfile.tpl";
																					}
																	}
																	else {
	//					$this->templateName = str_replace($whiteSpaces,"",urlencode($this->navigation[3].".tpl"));
																					if ($this->navigation[3] != $this->navigation[4])
																									$tpl_name = $this->navigation[3].$this->navigation[4];
																					else
																									$tpl_name = $this->navigation[4];
																					$this->templateName = str_replace($whiteSpaces,"",urlencode($tpl_name.".tpl"));
																	}
												}
												else {
																if ($this->navigation['1'] == 'Help') {
																				$this->templateName = "Documentation.tpl";
																}
																else {
																				$this->templateName = "Login.tpl";
																}
												}

												if ($debug)
																var_dump($this->templateName);
								}

		function getRadiusUsage()
		{
			for($i=0;$i<2;$i++) {
				for($j=0;$j<8;$j++) {
					$x = $this->data[vapSettings][vapSettingTable][wlan.$i][vap.$j][authenticationType];
					if (($x == 4) || ($x == 8) || ($x == 12)) {
						return 0;
					}
				}
			}

			return 1;
		}

        function setWdsVars()
        {
            $tmpMode0 = $this->data['wlanSettings']['wlanSettingTable']['wlan0']['apMode'];
            $tmpMode1 = $this->data['wlanSettings']['wlanSettingTable']['wlan1']['apMode'];

            if($this->config["P2P"]["status"]) {
                $assignMode0 = (($tmpMode0 == '1') || ($tmpMode0 == '2'))?$tmpMode0:'2';
                $assignMode1 = (($tmpMode1 == '1') || ($tmpMode1 == '2'))?$tmpMode1:'2';
                
                $tabSelect0 = (($tmpMode0 == '0') || ($tmpMode0 == '1') || ($tmpMode0 == '2'))?$assignMode0:'';
                $tabSelect1 = (($tmpMode1 == '0') || ($tmpMode1 == '1') || ($tmpMode1 == '2'))?$assignMode1:'';
                    
                $wdsArray['0'] = array( "tabLabel" => "Wireless Point&ndash;to&ndash;Point Bridge",
                                        "tabVal0"    => $assignMode0,
                                        "tabVal1"    => $assignMode1,
                                        "tabSelect0" => $tabSelect0,
                                        "tabSelect1" => $tabSelect1,
                                        "activeApMode0"  =>  ($tabSelect0!=''),
                                        "activeApMode1"  =>  ($tabSelect1!=''),
                                        "noOfWdsVaps"   => "1"
                );
            }

            if($this->config["P2MP"]["status"]) {
                $tabSelect0 = (($tmpMode0 == '3')||($tmpMode0 == '4'))?$tmpMode0:'';
                $tabSelect1 = (($tmpMode1 == '3')||($tmpMode1 == '4'))?$tmpMode1:'';

                $wdsArray['1'] = array( "tabLabel" => "Wireless Point to Multi&ndash;Point Bridge",
                                        "tabVal0"    => ((($tmpMode0 == '3')||($tmpMode0 == '4'))?$tmpMode0:'4'),
                                        "tabVal1"    => ((($tmpMode1 == '3')||($tmpMode1 == '4'))?$tmpMode1:'4'),
                                        "tabSelect0" => $tabSelect0,
                                        "tabSelect1" => $tabSelect1,
                                        "activeApMode0"  =>  ($tabSelect0!=''),
                                        "activeApMode1"  =>  ($tabSelect1!=''),
                                        "noOfWdsVaps"   => $this->config["NO_OF_WDS_VAPS"]["count"]

                );
            }

            if($this->config["CLIENT"]["status"]) {
                $tabSelect0 = (($tmpMode0 == '5'))?$tmpMode0:'';
                $tabSelect1 = (($tmpMode1 == '5'))?$tmpMode1:'';

                $wdsArray['2'] = array( "tabLabel" => "Client",
                                        "tabVal0"    => "5",
                                        "tabVal1"    => "5",
                                        "tabSelect0" => $tabSelect0,
                                        "tabSelect1" => $tabSelect1,
                                        "activeApMode0"  =>  ($tabSelect0!=''),
                                        "activeApMode1"  =>  ($tabSelect1!=''),
                                        "noOfWdsVaps"   => "0"

                );
            }
            return $wdsArray;

        }

								function setTemplateVars()
								{
                                    			$this->template->assign("navigation",$this->navigation);
												$this->template->assign("Login", $this->login);
												$this->template->assign("DocumentationLink", $this->getDocumentationLink());
												$this->template->assign("wdsData", $this->setWdsVars());
                                                if($this->confdEnable)
                                                    $this->template->assign("systime", `date`);
                                                else
                                                    $this->template->assign("systime", date("D M j G:i:s T Y"));
			$this->template->assign("random",rand(1000000,9999999));
			$this->template->assign("sessionEnabled",$this->session);

												$this->template->assign('errorString',$this->errorString);
			$this->template->assign('config',$this->config);

												switch ($this->mode) {
																case 'header':
																				$this->template->assign("mainMenu",$this->getMainMenu());
																				$this->template->assign("secondMenu",$this->getSecondMenu());
																				break;

																case 'thirdMenu':
																				$this->template->assign("thirdMenu", $this->getThirdMenu());
																				$this->template->assign("thirdMenuDisplay", (count($this->getThirdMenu()))-1);
																				break;

																case 'master':
																				$this->template->assign("templateName",$this->templateName);
																				if (!$this->config["DUAL_CONCURRENT"]["status"]) {
																					$this->template->assign("activeMode",$this->data['activeMode']);
																					$this->template->assign("interface",$this->getInterfaceString($this->data['activeMode']));
																					$this->template->assign("interfaceNum",$this->getInterfaceNum($this->data['activeMode']));
                                                                                    if ($this->config['TWOGHZ']['status'])
                                                                                        $this->template->assign("defaultMode0", $this->getDefaultMode('TWO'));
                                                                                    if ($this->config['FIVEGHZ']['status'])
                                                                                        $this->template->assign("defaultMode1", $this->getDefaultMode('FIVE'));
					}
																				else {
/*                        if ($this->data['radioStatus1'] == '1' && $this->data['radioStatus0'] != '1') {
																									$this->template->assign("interface",'wlan2');
																									$this->template->assign("interfaceNum",'1');
																								}
																								else {
																									$this->template->assign("interface",'wlan1');
																									$this->template->assign("interfaceNum",'0');
																								}*/
																				}
																				$this->template->assign("parentStr",$this->parentStr);
																				$this->template->assign("data",$this->data);
                                                                                
					$this->template->assign("radiusUsed",$this->getRadiusUsage());

																				if ($this->navigation[7]!= '') {
																								$this->template->assign("encryptionSel",$this->getEncryptionSelector());
																				}
                                                                                if ($this->config["FIVEGHZ"]["status"]) {
                                                                                    $this->template->assign("support5GHz",$this->get5GHzSupport());
                                                                                }

																				if ($this->navigation[3]=='Basic' && $this->navigation[2] == 'Wireless') {
						if ($this->config["TWOGHZ"]["status"]) {
																									if (!$this->config["DUAL_CONCURRENT"]["status"]) {
								$this->template->assign("ChannelList0",$this->getChannelList($this->data["activeMode"], false));
							}
							else {
								$this->template->assign("ChannelList0",$this->getChannelList($this->data["activeMode0"], false));
							}
							$this->template->assign("ChannelList_0",$this->getChannelList('0', true));
							if ($this->config["MODE11G"]["status"]) {
								$this->template->assign("ChannelList_1",$this->getChannelList('1', true));

								$this->template->assign("wlan0_40MHzSupport",$this->get40MHzSupport('wlan0'));
								$this->template->assign("DataRateList_0",$this->getDataRateList($this->data[wlanSettings][wlanSettingTable][wlan0][operateMode]));
								if ($this->config["MODE11N"]["status"]) {
									$this->template->assign("MCSRateList_0",$this->getMCSRateList($this->data[wlanSettings][wlanSettingTable][wlan0][channelWidth],$this->data[wlanSettings][wlanSettingTable][wlan0][guardInterval]));
									$this->template->assign("channelWidthList_0",$this->getChannelWidthList('0'));
									$this->template->assign("ext_protect_spacingList_0",$this->getExtProtectSpacingList());
                                                                        $this->template->assign("ext_chan_offsetList_0",$this->getExtChanOffsetList());
									$this->template->assign("guardIntervalList",$this->getWirelessParameters('GI'));
									$this->template->assign("ChannelList_0_20",$this->get11nChannelList('wlan0','20'));
									if ($this->get40MHzSupport('wlan0'))
										$this->template->assign("ChannelList_0_40",$this->get11nChannelList('wlan0','40'));
								}
							}
						}
						if ($this->config["FIVEGHZ"]["status"]) {
																									if (!$this->config["DUAL_CONCURRENT"]["status"]) {
								$this->template->assign("ChannelList1",$this->getChannelList($this->data["activeMode"], false));
							}
							else {
								$this->template->assign("ChannelList1",$this->getChannelList($this->data["activeMode1"], false));
							}
							$this->template->assign("ChannelList_3",$this->getChannelList('3', true));

							$this->template->assign("wlan1_40MHzSupport",$this->get40MHzSupport('wlan1'));

							$this->template->assign("DataRateList_1",$this->getDataRateList($this->data[wlanSettings][wlanSettingTable][wlan1][operateMode]));
																									if ($this->config["MODE11N"]["status"]) {
								$this->template->assign("MCSRateList_1",$this->getMCSRateList($this->data[wlanSettings][wlanSettingTable][wlan1][channelWidth],$this->data[wlanSettings][wlanSettingTable][wlan1][guardInterval]));
								$this->template->assign("channelWidthList_1",$this->getChannelWidthList('1'));
								$this->template->assign("guardIntervalList",$this->getWirelessParameters('GI'));
								$this->template->assign("ChannelList_1_20",$this->get11nChannelList('wlan1','20'));
								if ($this->get40MHzSupport('wlan1'))
									$this->template->assign("ChannelList_1_40",$this->get11nChannelList('wlan1','40'));
							}
						}
						$this->template->assign("outputPowerList",$this->getWirelessParameters('OP'));
																				}
																				else if ($this->navigation[0]=="General" || $this->navigation[1]=="Monitoring") {
																								$this->template->assign("countryList",$this->getCountryList());
						if ($this->config["TWOGHZ"]["status"]) {
																									$this->template->assign("wlan0ModeString",$this->getModeString($this->data[wlanSettings][wlanSettingTable][wlan0][operateMode]));
						}
						if ($this->config["FIVEGHZ"]["status"]) {
																									$this->template->assign("wlan1ModeString",$this->getModeString($this->data[wlanSettings][wlanSettingTable][wlan1][operateMode]));
						}
																				}
																				if ($this->navigation[0]=="Time") {
																								$this->template->assign("timeZones",$this->getTimeZones());
																				}
																				else if ($this->config['ETHERNET_CONFIG']["status"] && $this->navigation[3]=='Advanced' && $this->navigation[0] == 'Ethernet') {
																					$this->template->assign("speedList",$this->getSpeedList());
																				}

																				else if ($this->navigation[3]=='Advanced' && $this->navigation[2] == 'Wireless') {
						if ($this->config["WMM"]["status"])
							$this->template->assign("apEdcaCwList",$this->getApEdcaCwList());
																				}
																				else if ($this->config["SYSLOGD"]["status"] && $this->navigation[2] == "Logs") {
                                                                                        $this->template->assign("LogMessages",$this->getLogMessages());
                                                                                        }
																				else if ($this->navigation[2] == "Wireless Bridge") {
																					$this->template->assign("macValue",$this->returnAPMac());
																				}
																				$this->template->assign("authenticationTypeList",$this->getAuthenticationTypeList($this->navigation[9]));
					$this->template->assign("clientSeparationList",$this->getclientSeparationList());
					$this->template->assign("VLANTypeList",$this->getVLANTypeList());
					if ($this->navigation[7] != '' && $this->navigation[8] != '') {
																								$this->template->assign("encryptionTypeList",$this->getWepKeyTypeList(($this->navigation[9]=='')?$this->data['authenticationType']:$this->data['wdsAuthenticationtype']));
																				}
					if ($this->config["CLIENT"]["status"]) {
						$this->template->assign("clientAuthenticationTypeList",$this->getClientAuthenticationTypeList());
						$this->template->assign("clientEncryptionTypeList",$this->getEncryptionTypeList());
						if ($this->config["TWOGHZ"]["status"]) {
							$this->template->assign("sta0encryptionSel",$this->getStaEncryptionSelector('0'));
						}
						if ($this->config["FIVEGHZ"]["status"]) {
							$this->template->assign("sta1encryptionSel",$this->getStaEncryptionSelector('1'));
						}
					}
																				break;
												}
								}

function get5GHzSupport()
{
    if ($this->confdEnable) {
           if ($this->postedCountry != '')
            	$support = explode(' ',conf_get("system:monitor:5GhzSupport:".$this->postedCountry));
            else 
                $support = explode(' ',conf_get('system:monitor:5GhzSupport'));
     }
     else {
         $support = explode(' ','system:monitor:5GhzSupport yes');
     }
     return (($support[1]!='no')?true:false);
 }

								function getDocumentationLink()
								{
												$file = 'support.link';
												if (!file_exists($file)) return '';
												$fp = fopen($file, "rb");
												if (!$fp) return '';
												$str = file_get_contents($file);
												fclose($fp);
												$strArr = explode("\n",$str);
				if ($this->confdEnable) {
																$productIdArr = explode(' ', conf_get('system:monitor:productId'));
				$productId = $productIdArr[1];
												}
			else {
				$productId = "WNAP210";
			}
												if ($productId == 'WG102')
																return $strArr[1];
												else
																return $strArr[0];
								}

								function getLogMessages()
								{
												if ($this->confdEnable) {
															if($this->config["ARIES"]["status"]){
																$file = '/tmp/log/messages';
															}else {
																$file = '/var/log/messages';
															}
												}
												else {
																$file = "messages";
												}
												if (!file_exists($file)) return '';
												$fp = fopen($file, "rb");
												if (!$fp) return '';
												$str = file_get_contents($file);
												fclose($fp);
												$str .= "\n";
                                                return $str;
								}

								function getEncryptionSelector()
								{
												if ($this->navigation[9] != 'wdsprofile') {
																if (($this->data['authenticationType']=='0' || $this->data['authenticationType']=='1') && $this->data['encryption']=='1') {
																				return $this->data['wepKeyType'];
																}
																else {
																				return $this->data['encryption'];
																}
												}
												else {
																if (($this->data['wdsAuthenticationtype']=='0' || $this->data['wdsAuthenticationtype']=='1') && $this->data['wdsEncryption']=='1') {
																				return $this->data['wdsWepKeyType'];
																}
																else {
																				return $this->data['wdsEncryption'];
																}
												}
								}

								function getStaEncryptionSelector($mode)
								{
			if (($this->data['staSettings']['staSettingTable']['wlan'.$mode]['sta0']['authenticationType']=='0' ||
					$this->data['staSettings']['staSettingTable']['wlan'.$mode]['sta0']['authenticationType']=='1') &&
					$this->data['staSettings']['staSettingTable']['wlan'.$mode]['sta0']['encryption']=='1') {
				return $this->data['staSettings']['staSettingTable']['wlan'.$mode]['sta0']['wepKeyType'];
			}
			else {
				return $this->data['staSettings']['staSettingTable']['wlan'.$mode]['sta0']['encryption'];
			}
								}

								function getMainMenu()
								{
												return $this->mainMenu;
								}

								function getSecondMenu()
								{
												if (is_array($this->secondMenu))
																return ($this->secondMenu);
								}

								function getThirdMenu()
								{
												return $this->thirdMenu;
								}

								function displayTemplate()
								{
												header( "Cache-Control: no-store, no-cache, must-revalidate, max-age=0" );
												header( "Pragma: no-cache" );
												$this->template->display($this->mode.'.tpl');

												return true;
								}

								function getNavigation()
								{
												return $this->navigation;
								}

								function checkAction()
								{
												if ($_REQUEST["Action"])
																return true;
												else
																return false;
								}

								function doAction()
								{
												if (!$this->checkAction()) {
																return true;
												}
											else {
																if (!$this->confdEnable)
																				print_r($_POST);
												}
												if ($this->navigation[1]=='Maintenance') {
																if ($_POST['rebootAP'] == '1') {
																				$this->doReboot();
																}
																else if ($_POST['resetConfiguration'] == '1') {
																				if ($_SERVER['SERVER_PORT'] == '443')
																								$this->template->assign("ipAddress",'https://'.$this->getDefaultIpAddress().'/');
																				else
																								$this->template->assign("ipAddress",'http://'.$this->getDefaultIpAddress().'/');
					$this->template->assign("restoringDefaults",true);
																				$this->doRestoreConfig('/etc/default-config', false);
																}
                                                                                                                                else if($_POST['firwareFileName'] != ''){
                                                                                                                                    $this->doFirmwareUpgradeTFTP($_POST['firwareFileName'], $_POST['tftpServerIP']);
                                                                                                                                }
																else if (!empty($_FILES['firmwareFile']['name'])) {
																				if ($_FILES['firmwareFile']['size'] > 0 && $_FILES['firmwareFile']['error'] == 0)
																								$this->doFirmwareUpgrade();
																				else
																								$this->errorString = "Invalid file selected!";
																}
																else if (!empty($_FILES['restoreSettingsFile']['name'])) {
																				if ($_FILES['restoreSettingsFile']['size'] > 0 && $_FILES['restoreSettingsFile']['error'] == 0)
																								$this->doRestoreConfig('restoreSettingsFile',true);
																				else
																								$this->errorString = "Invalid file selected!";
																}
																else if ($_POST['backupSettings'] == 'Backup') {
																				Header('downloadFile.php?file=config');
																}
																else if ($_POST['restorePassword'] == '1') {
																				$arr['system']['basicSettings']['adminPasswd']=$this->getDefaultPassword();
																				$this->setData($arr);
																				$_SESSION=array();
																				header("Location: logout.html");  // Redirect to Login Page
																				die;

																}
																else if ($_POST['oldAdminPasswd'] != '') {
																				if ($_POST['oldAdminPasswd']!=(($this->confdEnable)?$this->getPassword():$this->getDefaultPassword())) {
																								$this->errorString = "The current password entered is incorrect!";
																				}
																				else if (empty($_POST['adminPasswdConfirm'])) {
																								$this->errorString = "New password cannot be empty!";
																				}
																				else {
																								$this->setData($_POST);
																								$_SESSION=array();
																								header("Location: logout.html");  // Redirect to Login Page
																								die;
																				}
																}
																else {
																				$this->setData($_POST);
																}
												}
												else if ($this->navigation[1]=='Configuration') {
                                                    			if (!empty($_FILES['macListFile'.($_POST['previousInterfaceNum']-1)]['name'])) {
                                                    							$filename = $_FILES['macListFile'.($_POST['previousInterfaceNum']-1)]['tmp_name'];
																				exec("dos2unix $filename", $dummy, $res);
																				$str = file_get_contents($_FILES['macListFile'.($_POST['previousInterfaceNum']-1)]['tmp_name']);
																				$macList = array_filter(explode("\n",str_replace('\n\n','\n',$str)));
																				$validMacList = preg_grep("/^([\t]*)([0-9a-fA-F][0-9a-fA-F]\:){5}([0-9a-fA-F][0-9a-fA-F])([\t]*)$/i",$macList);
																				if (!(count($macList) > 0)) {
																								$this->errorString = "Invalid input file selected!";
																				}
																				else if (count($validMacList) != count($macList)) {
																								$this->errorString = "Invalid MAC address in input file!";
																				}
																				else {
																								foreach ($macList as $mac) {
																												if (!empty($mac))
																																if (($_POST['previousInterfaceNum']-1)==0)
																																				$_POST[system][apList][knownApTable][wlan0].=strtoupper(str_replace(':','-',$mac)).',';
																																else if (($_POST['previousInterfaceNum']-1)==1)
																																				$_POST[system][apList][knownApTable][wlan1].=strtoupper(str_replace(':','-',$mac)).',';
																								}
																								if ($_POST['merge_rogue_ap_list'.($_POST['previousInterfaceNum']-1)] == 1) {
																												if (($_POST['previousInterfaceNum']-1)==0)
																																$_POST[delete][apList][knownApTable][wlan0] = str_replace(':','-',$_POST[apList][knownApTable][wlan0]);
																												else if (($_POST['previousInterfaceNum']-1)==1)
																																$_POST[delete][apList][knownApTable][wlan1] = str_replace(':','-',$_POST[apList][knownApTable][wlan1]);
																								}
																				}
																}
				else if ($this->navigation[4] == 'IP Settings') {
					$staticIp = new ipAddress($_POST['system']['basicSettings']['ipAddr'],$_POST['system']['basicSettings']['netmaskAddr']);
					$gateway = new ipAddress($_POST['system']['basicSettings']['gatewayAddr'],$_POST['system']['basicSettings']['netmaskAddr']);
					if (!ipAddress::inSameSubnet($staticIp,$gateway) && $_POST['system']['basicSettings']['gatewayAddr'] != '0.0.0.0') {
						$this->errorString = 'Gateway IP should be matching Management Network!!';
						return false;
					}
					if ($this->config["DHCPSERVER"]["status"]) {
						if ($this->checkVlanId()) {
							if ($_POST['system']['basicSettings']['dhcpClientStatus']=='0' && $this->getDhcpServerStatus()) {
								$ipPool = $this->getDHCPPool();
								if ($staticIp->ipInPool($ipPool[0],$ipPool[1])) {
									$this->errorString = 'IP is in DHCP Server IP Pool!';
									return false;
								}
							}
							else if ($this->getDhcpServerStatus()) {
								$this->errorString = 'DHCP Server is already running on Management VLAN!';
								return false;
							}
						}
					}
																}
																else if ($this->config["DHCPSERVER"]["status"] && $this->navigation[4] == 'DHCP Server Settings') {
																				if($_POST['system']['dhcpsSettings']['dhcpServerStatus'] != '0') {
																									$staticIp = $this->getStaticIpAddress();
																									$pool = new ipAddress($_POST['system']['dhcpsSettings']['dhcpsIpStart'],$_POST['system']['dhcpsSettings']['dhcpsNetMask']);
																									$poolEnd = new ipAddress($_POST['system']['dhcpsSettings']['dhcpsIpEnd'],$_POST['system']['dhcpsSettings']['dhcpsNetMask']);
																									$gateway = new ipAddress($_POST['system']['dhcpsSettings']['dhcpsGateway'],$_POST['system']['dhcpsSettings']['dhcpsNetMask']);
																									if (!$pool->ipLT($poolEnd)) {
																													$this->errorString = 'Starting IP should be less than Ending IP!';
																													return false;
																									}
																									else if (!ipAddress::inSameSubnet($pool,$gateway)) {
																													$this->errorString = 'Gateway IP should be matching Management Network!!';
																													return false;
																									}
																								if ($this->checkVlanId($_POST['system']['dhcpsSettings']['dhcpsVlanId'])) {
																													if ($_POST['system']['dhcpsSettings']['dhcpServerStatus']=='1') {
																																	if ($this->getDhcpClientStatus()) {
																																					$this->errorString = 'DHCP client is already running on Management VLAN!';
																																					return false;
																																	}
																																	else {
																																					if ($staticIp->ipInPool($pool,$poolEnd)) {
																																									$this->errorString = 'DHCP Server IP Pool conflicts with Management IP!';
																																									return false;
																																					}
																																					else if (!ipAddress::inSameSubnet($staticIp,$pool)) {
																																									$this->errorString = 'DHCP Server IP Pool should be matching Management Network!';
																																									return false;
																																					}
																																	}
																													}
																									}
																					}
																}
                                                                $this->clientModeSet = 0;
                                                                if($this->config["WN802TV2"]["status"]){
                                                                    if ($_POST['system']["\'wlanSettings\'"]["\'wlanSettingTable\'"]["\'wlan0\'"]["\'apMode\'"] == '5') {
                                                                        $this->clientModeSet = 1;
                                                                    }
                                                                }

																$this->setData($_POST);
                                                                }
																if ($this->config["TR69"]["status"]){
									$flag = false;
                                                                    if ($_REQUEST['menu3'] == 'Profile Settings') {
                                                                        $int = key($_POST['system']["\'vapSettings\'"]["\'vapSettingTable\'"]);
                                                                        $vap = key($_POST['system']["\'vapSettings\'"]["\'vapSettingTable\'"][$int]);
                                                                        $x = $_POST['system']["\'vapSettings\'"]["\'vapSettingTable\'"][$int][$vap]["\'authenticationType\'"];
                                                                        if (($x == 2) || ($x == 4) || ($x == 8) || ($x == 12) || ($x == '2') || ($x == '4') || ($x == '8') || ($x == '12')) {
                                                                        	$flag = true;
									}
                                                                    }
								    elseif($_REQUEST['menu4'] == 'MAC Authentication'){
                                                                        $int = key($_POST['system']["\'wlanSettings\'"]["\'wlanSettingTable\'"]);
                                                                        $x = $_POST['system']["\'wlanSettings\'"]["\'wlanSettingTable\'"][$int]["\'accessControlMode\'"];
									if(($x == 2) || ($x == '2')){
										$flag = true;
									}
								    }
								    if($flag){
                                                                            conf_set_buffer("system:wlanSettings:wlanSettingTable:".str_replace("\'",'',$int).":authenticationServiceMode 1");
																			conf_save();

								    }		
                                                                }
												if ($this->countryChanged ==  true) {
																$this->doReboot();
												}

			if ($this->config["CLIENT"]["status"]) {
				if ($this->config["TWOGHZ"]["status"] && (($this->oldApMode0 == '5' || $this->newApMode0 =='5') && ($this->oldApMode0 != $this->newApMode0) && !($this->oldApMode0 != '5' && $this->newApMode0 !='5'))) {
						$this->doReboot();
				}
				if ($this->config["FIVEGHZ"]["status"] && (($this->oldApMode1 == '5' || $this->newApMode1 =='5') && ($this->oldApMode1 != $this->newApMode1) && !($this->oldApMode1 != '5' && $this->newApMode1 !='5'))) {
					$this->doReboot();
				}
			}
            }

		function getDefaultIpAddress()
		{
			if ($this->confdEnable) {
				$defaultIP = explode(' ',exec("grep system:basicSettings:ipAddr /etc/default-config"));
			}
			else {
				$defaultIP = explode(' ',"system:basicSettings:ipAddr 192.168.0.236");
			}
			return $defaultIP[1];
		}

								function getStaticIpAddress()
								{
												if ($this->confdEnable) {
																	$staticIp = explode(' ',conf_get("system:basicSettings:ipAddr"));
																	$ipMask = explode(' ',conf_get("system:basicSettings:netmaskAddr"));
													}
													else {
																	$staticIp = explode(' ',"system:basicSettings:ipAddr 192.168.0.230");
																	$ipMask = explode(' ',"system:basicSettings:netmaskAddr 255.255.255.0");
													}
													return new ipAddress($staticIp[1],$ipMask[1]);
									}

									function getDhcpClientStatus()
									{
												if ($this->confdEnable) {
																	$status = explode(' ',conf_get("system:basicSettings:dhcpClientStatus"));
													}
													else {
																	$status = explode(' ',"system:basicSettings:dhcpClientStatus 0");
													}
													return ($status[1]=='1');
									}

									function getDhcpServerStatus()
									{
												if ($this->confdEnable) {
																	$status = explode(' ',conf_get("system:dhcpsSettings:dhcpServerStatus"));
													}
													else {
																	$status = explode(' ',"system:dhcpsSettings:dhcpServerStatus 0");
													}
													return ($status[1]=='1');
									}

								function getDHCPPool()
								{
												if ($this->confdEnable) {
																	$ipStart = explode(' ',conf_get("system:dhcpsSettings:dhcpsIpStart"));
																	$ipEnd = explode(' ',conf_get("system:dhcpsSettings:dhcpsIpEnd"));
																	$ipMask = explode(' ',conf_get("system:dhcpsSettings:dhcpsNetMask"));
													}
													else {
																	$ipStart = explode(' ',"system:dhcpsSettings:dhcpsIpStart 192.168.0.2");
																	$ipEnd = explode(' ',"system:dhcpsSettings:dhcpsIpEnd 192.168.0.50");
																	$ipMask = explode(' ',"system:dhcpsSettings:dhcpsNetMask 255.255.255.0");
													}
													$ipPool[0] = new ipAddress($ipStart[1],$ipMask[1]);
													$ipPool[1] = new ipAddress($ipEnd[1],$ipMask[1]);
													return $ipPool;
									}

									function checkVlanId($dhcpVlan='')
									{
													if ($this->confdEnable) {
																	$dhcpVlanArr = explode(' ',conf_get("system:dhcpsSettings:dhcpsVlanId"));
																	$mgmtVlan = explode(' ',conf_get("system:basicSettings:managementVlanID"));
													}
													else {
																	$dhcpVlanArr = explode(' ',"system:dhcpsSettings:dhcpsVlanId 1");
																	$mgmtVlan = explode(' ',"system:basicSettings:managementVlanID 1");
													}
													if ($dhcpVlan == '')
																	$dhcpVlan = $dhcpVlanArr[1];

													if ($dhcpVlan == $mgmtVlan[1])
																	return true;
													else
																	return false;
									}

								function doUploadFile($fieldName, $targetFileName)
								{
												$this->target_path = "/tmp/";
												$this->target_path = $this->target_path . $targetFileName;
												if(!@move_uploaded_file($_FILES[$fieldName]['tmp_name'], $this->target_path)) {
																$this->errorString = "There was an error uploading the file, please try again!";
																return false;
												}
												return true;
								}

								function doFirmwareUpgrade()
								{
												if ($this->doUploadFile('firmwareFile', basename( $_FILES['firmwareFile']['name']))) {
																exec("/usr/local/bin/firmware-error-check ".$this->target_path,$dummy, $res);
																unset($dummy); // for releasing the memory
																if ($res != 0) {
																				$this->errorString = "There was an error upgrading the firmware, please check the system log!";
																}
																else {
											$this->template->assign("random",rand(1000000,9999999));
					$this->template->assign("redirectTime",240000);
														$this->template->display('progress.tpl');
					proc_close(proc_open("/usr/local/bin/firmware-upgrade-file ".$this->target_path." skip_error_check &",array(), $res));
					die;
																}
												}
								}
                                                                function doFirmwareUpgradeTFTP($fileName, $TFTPServer){
                                                                    exec("tftp -g -l /tmp/firmware.tar -r ". $fileName ." ". $TFTPServer,$dummy, $res);
                                                                    if ($res != 0) {
                                                                                echo "<script language='JavaScript'> window.top.frames['master'].document.location.href='index.php?page=master&menu1=Maintenance&menu2=Upgrade&menu3=Firmware Upgrade TFTP&tftpfail=1&menu4=';</script>";
                                                                               die;
                                                                    }
                                                                    else {
                                                                    
                                                                        exec("/usr/local/bin/firmware-error-check /tmp/firmware.tar",$dummy, $res);
                                                                        unset($dummy); // for releasing the memory
                                                                        if ($res != 0) {
                                                                                                        $this->errorString = "There was an error upgrading the firmware, please check the system log!";
                                                                        }
                                                                        else {
                                                                            proc_close(proc_open("/usr/local/bin/firmware-upgrade-file /tmp/firmware.tar skip_error_check &",array(), $res));
                                                                            $this->template->assign("random",rand(1000000,9999999));
                                                                            $this->template->assign("redirectTime",240000);
                                                                            $this->template->display('progress.tpl');
                                                                            die;
                                                                        }
                                                                    }
                                                                }

								function doRestoreConfig($fileName, $flag=false)
								{
												if ($flag == true) {
																if ($this->doUploadFile($fileName, 'userConfig')) {
																				exec("/usr/local/bin/verify-config.sh $this->target_path",$dummy,$verify);
																				if ($verify) {
																								$this->errorString = "Invalid Configuration File!";
																				}
																				else {
																								exec("/usr/local/bin/restore-configuration $this->target_path no-reboot",$dummy, $res);
																								$this->doReboot();
																				}
																}
												}
												else {
																exec("/usr/local/bin/restore-configuration $fileName no-reboot",$dummy, $res);
																$this->doReboot();
												}
								}

								function doReboot()
								{
			$this->removeSessionLink();
			$this->template->assign("redirectTime",90000);
												proc_close(proc_open("reboot -d 5 &",array(),$res));
												$this->displayProgress();
								}

								function displayProgress(){
			$this->template->assign("random",rand(1000000,9999999));
												$this->template->display('progress.tpl');
												die;
								}

								function getModeString($mode)
								{
			if ($this->config["TWOGHZ"]["status"]) {
				$modeMap['0']	=	'b';
				$modeMap['1']	=	'bg';
				$modeMap['2']	=	'ng';
			}
			if ($this->config["FIVEGHZ"]["status"]) {
				$modeMap['3']	=	'a';
				$modeMap['4']	=	'na';
			}
												return $modeMap[$mode];
								}

								function getWidthString($mode, $width)
								{
												if ($width == '0')
																return '20MHz';
												else
												{
																$modeMap = array(       '0' => '20MHz', '1' =>      '20MHz', '2'       =>      '40MHz', '3'       =>      '20MHz', '4'        =>      '40MHz');
																return $modeMap[$mode];
												}
								}

		function getInterfaceNum($mode)
								{
			if ($this->config["TWOGHZ"]["status"]) {
				$modeArray['0']	=	'0';
				$modeArray['1']	=	'0';
				$modeArray['2']	=	'0';
			}
			if ($this->config["FIVEGHZ"]["status"]) {
				$modeArray['3']	=	'1';
				$modeArray['4']	=	'1';
			}
												return $modeArray[$mode];
								}


								function getInterfaceString($mode)
								{
			if ($this->config["TWOGHZ"]["status"]) {
				$modeArray['0']	=	'wlan1';
				$modeArray['1']	=	'wlan1';
				$modeArray['2']	=	'wlan1';
			}
			if ($this->config["FIVEGHZ"]["status"]) {
				$modeArray['3']	=	'wlan2';
				$modeArray['4']	=	'wlan2';
			}
												return $modeArray[$mode];
								}

								function getChannelWidthList($mode)
								{
												$support = $this->get40MHzSupport('wlan'.$mode);

												if ($support == '0') {
																return array('0'=>'20 MHz');
												}
												else {
																return array('2'=>'Dynamic 20/40 MHz','0'=>'20 MHz','1'=>'40 MHz');
												}
								}

								function getExtProtectSpacingList()
								{
                                                                                                                                return array('0'=>'Auto','1'=>'20','2'=>'25');
								}

                                                                function getExtChanOffsetList()
                                                                {
                                                                                                                                return array('0'=>'Auto','1'=>'Upper','-1'=>'Lower');
								}

								function getWirelessParameters($param)
								{
												if ($param == 'GI') {
																return array('0'=>'Auto','800'=>'Long - 800 ns');
												}
												else if ($param == 'OP') {
																return array('0'=>'Full','1'=>'Half','2'=>'Quarter','3'=>'Eighth','4'=>'Minimum');
												}
												else {
																return array();
												}
								}

								function getSpeedList()
								{
									$speedList = array( "0" => "10 Mbps Half Duplex",
								"1" => "10 Mbps Full Duplex",
														"2" => "100 Mbps Half Duplex",
								"3" => "100 Mbps Full Duplex ");
									return $speedList;
								}

		function getclientSeparationList()
		{
				if ($this->confdEnable) {
																$productIdArr = explode(' ', conf_get('system:monitor:productId'));
				$productId = $productIdArr[1];
												}
			else {
				$productId = "WNAP210";
			}

												if ($productId == 'WG102') {
				$clientSeparationList = array("0" => "Disable", "1" => "Unicast", "2" => "Multicast", "3" => "Enable" );
			}
			else {
				$clientSeparationList = array("0" => "Disable", "3" => "Enable");
			}
			return $clientSeparationList;
		}
		function getVLANTypeList(){
                    $VLANTyleList = array("0" => "Disable", "1" => "Optional", "2" => "Required");
                    return $VLANTyleList;
                }

								function returnData()
								{
												//if ($_REQUEST['mode9']=='wdsprofile') {
																print_r($this->data);
												//}]
								}

								function getAuthenticationTypeList()
								{
												if ($this->navigation[2] != 'Wireless Bridge') {
																$retArr["0"]	=	"Open System";
				$retArr["1"]	=	"Shared Key";
				if ($this->config["MBSSID"]["status"]) {
					$retArr["2"]	=	"Legacy 802.1X";
				}

				$retArr["4"]	=	"WPA with Radius";
				$retArr["8"]	=	"WPA2 with Radius";
				$retArr["12"]	=	"WPA &amp; WPA2 with Radius";
				$retArr["16"]	=	"WPA-PSK";
				$retArr["32"]	=	"WPA2-PSK";
				$retArr["48"]	=	"WPA-PSK &amp; WPA2-PSK";
			}
												else {
																$retArr	=	array(	"0"	=>	"Open System",
									"2"	=>	"WPA-PSK",
									"4"	=>	"WPA2-PSK"
								);
												}
			return $retArr;
								}

								function getClientAuthenticationTypeList()
								{
												return array(	"0"	=>	"Open System",
																												"1"	=>	"Shared Key",
																												"16"	=>	"WPA-PSK",
																												"32"	=>	"WPA2-PSK"
																								);
								}

								function getWepKeyTypeList($authType)
								{
												if ($this->navigation[9]=='') {
																switch ($authType) {
																				case '0': //open
																								return array(	"0"	=>	"None",
																																								"64"	=>	"64 bit WEP",
																																								"128"	=>	"128 bit WEP",
																																								"152"	=>	"152 bit WEP"
																																				);
																								break;

																				case '1': //shared
																								return array(	"64"	=>	"64 bit WEP",
																																								"128"	=>	"128 bit WEP",
																																								"152"	=>	"152 bit WEP"
																																				);
																								break;

																				case '2': //Legacy 802.1X
																								return array(	"0"	=>	"None"	);
																								break;

																				case '4': //wpa with radius
																				case '16': //wpa-psk
																								return array(	"2"	=>	"TKIP",
																																								"6"	=>	"TKIP+AES"
																																				);
																								break;

																				case '8': //wpa2 with radius
																				case '32': //wpa2-psk
																								return array(	"4"	=>	"AES",
																																								"6"	=>	"TKIP+AES"
																																				);
																								break;

																				case '12': //wpaORwpa2 with radius
																				case '48': //wpaORwpa2-psk
																								return array(	"6"	=>	"TKIP+AES"	);
																								break;
																}
												}
												else {
																switch ($authType) {
																				case '0': //open
																								return array(	"0"	=>	"None",
																																								"64"	=>	"64 bit WEP",
																																								"128"	=>	"128 bit WEP",
																																								"152"	=>	"152 bit WEP"
																																				);
																								break;

																				case '1': //shared
																								return array(	"64"	=>	"64 bit WEP",
																																								"128"	=>	"128 bit WEP",
																																								"152"	=>	"152 bit WEP"
																																				);
																								break;

																				case '2': //wpa psk
																								return array(	"2"	=>	"TKIP"	);
																								break;

																				case '4': //wpa2 psk
																								return array(	"4"	=>	"AES"	);
																								break;

																}
												}
								}

								function getEncryptionTypeList()
								{
												return array(	'0' =>	array(	"0"	=>	"None",
																																			"64"	=>	"64 bit WEP",
																																			"128"	=>	"128 bit WEP",
																																			"152"	=>	"152 bit WEP"
																															),
							'1'	=>	array(	"64"	=>	"64 bit WEP",
																																			"128"	=>	"128 bit WEP",
																																			"152"	=>	"152 bit WEP"
																															),
							'16'	=>	array(	"2"	=>	"TKIP"	),
							'32'	=>	array(	"4"	=>	"AES"	));
								}

								function jsEncode($array)
								{
												foreach($array as $key=>$val)
																$arr[] = "\"$key\":\"$val\"";
												if (is_array($arr))
																return '{'.implode(', ', $arr).'}';
												else
																return '{}';
								}

								function getChannelList($mode,$Json = false)
								{
												$str = "";
			if ($mode =='current') {
				if ($this->config["TWOGHZ"]["status"]) {
					$mode = ($this->config["MODE11G"]["status"])?(($this->config["MODE11N"]["status"])?'2':'1'):'0';
				}
				else if ($this->config["FIVEGHZ"]["status"] && $this->config["DUAL_CONCURRENT"]["status"]) {
					$mode = ($this->config["MODE11N"]["status"])?'4':'3';
				}
			}
			if ($this->confdEnable) {
																switch($mode) {
																				case '0':
																								$str = conf_get('system:monitor:channel20MHz:11b');
																								break;
																				case '1':
																								$str = conf_get('system:monitor:channel20MHz:11bg');
																				break;
																				case '3':
																								$str = conf_get('system:monitor:channel20MHz:11a');
																								break;
					case '2':
					case '4':

                                                $str = 'system:monitor:channel';
                                                $str .= $this->getWidthString($mode, $this->data[wlanSettings][wlanSettingTable]['wlan'.$this->getInterfaceNum($mode)]['channelWidth']);
                                                $str .= ':11'.$this->getModeString($mode);
                                                $str = conf_get($str);
																				break;
																	}
												}
												else {
																switch($mode) {
																				case '0':
																								$str = "system:monitor:channel20MHz:11b Auto;1/2.412GHz;2/2.417GHz;3/2.422GHz;4/2.427GHz;5/2.432GHz;6/2.437GHz;7/2.442GHz;8/2.447GHz;9/2.452GHz";
																								break;
																				case '1':
																								$str = "system:monitor:channel20MHz:11bg Auto;1/2.412GHz;2/2.417GHz;3/2.422GHz;4/2.427GHz;5/2.432GHz;6/2.437GHz;7/2.442GHz;8/2.447GHz;9/2.452GHz;10/2.457GHz;11/2.462GHz;12/2.467GHz;13/2.472GHz";
																								break;
																				case '3':
																								$str = "system:monitor:channel20MHz:11a Auto;149/5.745GHz;153/5.765GHz;157/5.785GHz;161/5.805GHz;165/5.825GHz";
																								break;
																				case 'current':
					case '2':
					case '4':
																									if ($this->getInterfaceString($this->data['activeMode']) == 'wlan1') {
																													$str = "system:monitor:channel:wlan0 Auto;1/2.412GHz;2/2.417GHz;3/2.422GHz;4/2.427GHz;5/2.432GHz;6/2.437GHz;7/2.442GHz;8/2.447GHz;9/2.452GHz;10/2.457GHz;11/2.462GHz";
																									}
																									else {
																													$str = "system:monitor:channel:wlan1 Auto;36/5.180GHz;40/5.200GHz;42/5.210GHz;44/5.220GHz;48/5.240GHz;50/5.250GHz;52/5.260GHz;56/5.280GHz;58/5.290GHz;60/5.300GHz;64/5.320GHz;149/5.745GHz;152/5.760GHz;153/5.765GHz;157/5.785GHz;160/5.800GHz;161/5.805GHz;165/5.825GHz";
																									}
																								break;
																}
												}
												$tmp=explode(' ', $str);

												$channelListArr=explode(';',$tmp[1]);
												unset($tmp);
												foreach ($channelListArr as $freq) {
																if ($freq != 'Auto') {
																				$key=explode('/',$freq);
																				$listArr[$key[0]]=$freq;
																}
																else {
																				$listArr[0]=$freq;
																}
												}
												if (count(array_filter($listArr))==0) {
																$listArr[0] = 'Auto';
												}
												unset($channelListArr);
												if ($Json) {
																return $this->jsEncode(array_filter($listArr));
												}
												else {
																return array_filter($listArr);
												}
								}

								function get40MHzSupport($interface)
								{
												if ($this->confdEnable)
																$supportArr = explode(' ', conf_get('system:monitor:40MhzSupport:'.$interface));
												else
																$supportArr = explode(' ', 'system:monitor:40MhzSupport:'.$interface.' yes');

												if ($supportArr[1] == 'no') {
																return 0;
												}
												else
																return 1;

								}

								function get40MHzSupportedCountry($country)
								{
												if ($this->confdEnable)
																$supportArr = explode(' ', conf_get('system:monitor:40MhzSupport:wlan1:'.$country));
												else
																$supportArr = explode(' ', 'system:monitor:40MhzSupport:wlan1:'.$country.' yes');
												if ($supportArr[1] == 'no') {
																return false;
												}
												else
																return true;

									}

								function get11nChannelList($interface,$band)
								{
												$str = "";
												$mode = ($interface=='wlan1')?'11na':'11ng';
												if ($this->confdEnable) {
																$str = conf_get('system:monitor:channel'.$band.'MHz:'.$mode);
												}
												else {
																if ($interface == 'wlan0') {
																				$str = "system:monitor:channel".$band."MHz:$mode Auto;1/2.412GHz;2/2.417GHz;3/2.422GHz;4/2.427GHz;5/2.432GHz;6/2.437GHz;7/2.442GHz;8/2.447GHz;9/2.452GHz;10/2.457GHz;11/2.462GHz";
																}
																else {
																				$str = "system:monitor:channel".$band."MHz:$mode Auto;36/5.180GHz;40/5.200GHz;42/5.210GHz;44/5.220GHz;48/5.240GHz;50/5.250GHz;52/5.260GHz;56/5.280GHz;58/5.290GHz;60/5.300GHz;64/5.320GHz;149/5.745GHz;152/5.760GHz;153/5.765GHz;157/5.785GHz;160/5.800GHz;161/5.805GHz;165/5.825GHz";
																}
												}
												$tmp=explode(' ', $str);

												$channelListArr=explode(';',$tmp[1]);
												unset($tmp);
												foreach ($channelListArr as $freq) {
																if ($freq != 'Auto') {
																				$key=explode('/',$freq);
																				$listArr[$key[0]]=$freq;
																}
																else {
																				$listArr[0]=$freq;
																}
												}
												if (count(array_filter($listArr))==0) {
																$listArr[0] = 'Auto';
												}
												unset($channelListArr);
												return $this->jsEncode(array_filter($listArr));
								}

								function getDataRateArray($arr, $flag=false) {
												$listArr = array();
												foreach ($arr as $key => $val) {
																if ($val != 'Best') {
																				$value = explode (' ',$val);
																				$valStr ='';
																				if ($flag) {
																								$valStr = (int)($key-1)." / $val";
																								$valueKey = (int)($key-1);
																				}
																				else {
																								$valStr = $val;
																								$valueKey = ((float)($value[0])*2);
																				}

																				$listArr[$valueKey] = $valStr;
																}
																else {
																				if ($flag)
																								$listArr[99] = $val;
																				else
																								$listArr[0] = $val;
																}

												}
												return $listArr;
								}


								function getDataRateList($mode)
								{

												switch ($mode) {
																case '0':
																				return $this->getDataRateArray(array("Best","1 Mbps","2 Mbps","5.5 Mbps","11 Mbps"));
																				break;

																case '1':
																				return $this->getDataRateArray(array("Best","1 Mbps","2 Mbps","5.5 Mbps","6 Mbps","9 Mbps","11 Mbps","12 Mbps","18 Mbps","24 Mbps","36 Mbps","48 Mbps","54 Mbps"));
																				break;

																case '3':
																				return $this->getDataRateArray(array("Best","6 Mbps","9 Mbps","12 Mbps","18 Mbps","24 Mbps","36 Mbps","48 Mbps","54 Mbps"));
																				break;
												}
								}

								function getMCSRateList($bw='0',$gi='0')
								{
												$bw=($bw=='2'?'1':$bw);

												if ($bw == '0' && $gi == '0') {
																return $this->getDataRateArray(array("Best", "7.2 Mbps", "14.4 Mbps", "21.7 Mbps", "28.9 Mbps", "43.3 Mbps", "57.8 Mbps", "65 Mbps", "72.2 Mbps", "14.44 Mbps", "28.88 Mbps", "43.33 Mbps", "57.77 Mbps", "86.66 Mbps", "115.56 Mbps", "130 Mbps", "144.44 Mbps"), true);
												}
												else if ($bw == '0' && $gi == '800') {
																return $this->getDataRateArray(array("Best", "6.5 Mbps", "13 Mbps", "19.5 Mbps", "26 Mbps", "39 Mbps", "52 Mbps", "58.5 Mbps", "65 Mbps", "13 Mbps", "26 Mbps", "39 Mbps", "52 Mbps", "78 Mbps", "104 Mbps", "117 Mbps", "130 Mbps"), true);
												}
												else if ($bw == '1' && $gi == '0') {
																return $this->getDataRateArray(array("Best", "15 Mbps", "30 Mbps", "45 Mbps", "60 Mbps", "90 Mbps", "120 Mbps", "135 Mbps", "150 Mbps", "30 Mbps", "60 Mbps", "90 Mbps", "120 Mbps", "180 Mbps", "240 Mbps", "270 Mbps", "300 Mbps"), true);
												}
												else if ($bw == '1' && $gi == '800') {
																return $this->getDataRateArray(array("Best", "13.5 Mbps", "27 Mbps", "40.5 Mbps", "54 Mbps", "81 Mbps", "108 Mbps", "121.5 Mbps", "135 Mbps", "27 Mbps", "54 Mbps", "81 Mbps", "108 Mbps", "162 Mbps", "216 Mbps", "243 Mbps", "270 Mbps"), true);
												}
								}

								function getApEdcaCwList()
								{
												return array(	"0"	=>	"0",
																												"1"	=>	"1",
																												"3"	=>	"3",
																												"7"	=>	"7",
																												"15"	=>	"15",
																												"31"	=>	"31",
																												"63"	=>	"63",
																												"127"	=>	"127",
																												"255"	=>	"255",
																												"511"	=>	"511",
																												"1023"	=>	"1023"
																								);
								}

								function getCountryList()
								{
												if ($this->confdEnable) {
																$str = explode(' ',conf_get('system:monitor:region'));
												}
												else {
																$str = explode(' ','system:monitor:region NA');
												}

												if (strtoupper($str[1]) == 'NA') {
																$countryList	=	array(
																																												"840"	=>	"United States",
																																												"5001"	=>	"Canada"
																																								);
												 if($this->config["ARIES"]["status"])
													{
														 unset($countryList["5001"]);
														 $countryList["124"] = "Canada";	
													}
												}
												else {
																$countryList	=	array(	"840"	=>	"United States",
																																												"8"		=>	"Albania",
																																												"51"	=>	"Armenia",
																																												"32"	=>	"Argentina",
																																												"40"	=>	"Austria",
																																												"5000"	=>	"Australia",
																																												"31"	=>	"Azerbaijan",
																																												"56"	=>	"Belgium",
																																												"100"	=>	"Bulgaria",
																																												"48"	=>	"Bahrain",
																																												"96"	=>	"Brunei",
																																												"68"	=>	"Bolivia",
																																												"76"	=>	"Brazil",
																																												"112"	=>	"Belarus",
																																												"84"	=>	"Belize",
																																												"5001"	=>	"Canada",
																																												"756"	=>	"Switzerland",
																																												"152"	=>	"Chile",
																																												"156"	=>	"China",
																																												"170"	=>	"Colombia",
																																												"188"	=>	"Costa Rica",
																																												"196"	=>	"Cyprus",
																																												"203"	=>	"Czech Republic",
																																												"276"	=>	"Germany",
																																												"208"	=>	"Denmark",
																																												"214"	=>	"Dominican Republic",
																																												"12"	=>	"Algeria",
																																												"218"	=>	"Ecuador",
																																												"233"	=>	"Estonia",
																																												"818"	=>	"Egypt",
																																												"724"	=>	"Spain",
																																												"246"	=>	"Finland",
																																												"250"	=>	"France",
																																												"826"	=>	"United Kingdom",
																																												"268"	=>	"Georgia",
																																												"300"	=>	"Greece",
																																												"320"	=>	"Guatemala",
																																												"344"	=>	"Hongkong",
																																												"340"	=>	"Honduras",
																																												"191"	=>	"Croatia",
																																												"348"	=>	"Hungary",
																																												"360"	=>	"Indonesia",
																																												"372"	=>	"Ireland",
																																												"376"	=>	"Israel",
																																												"356"	=>	"India",
																																												"364"	=>	"Iran",
																																												"352"	=>	"Iceland",
																																												"380"	=>	"Italy",
																																												"400"	=>	"Jordan",
																																												"392"	=>	"Japan",
																																												"408"	=>	"North Korea",
																																												"410"	=>	"Korea Republic",
																																												"414"	=>	"Kuwait",
																																												"398"	=>	"Kazakhstan",
																																												"422"	=>	"Lebanon",
																																												"438"	=>	"Liechtenstein",
																																												"440"	=>	"Lithuania",
																																												"442"	=>	"Luxembourg",
																																												"428"	=>	"Latvia",
																																												"504"	=>	"Morocco",
																																												"492"	=>	"Monaco",
																																												"807"	=>	"Macedonia",
																																												"446"	=>	"Macau",
																																												"470"	=>	"Malta",
																																												"484"	=>	"Mexico",
																																												"458"	=>	"Malaysia",
																																												"528"	=>	"Netherlands",
																																												"578"	=>	"Norway",
																																												"554"	=>	"New Zealand",
																																												"512"	=>	"Oman",
																																												"591"	=>	"Panama",
																																												"604"	=>	"Peru",
																																												"608"	=>	"Philippines",
																																												"586"	=>	"Pakistan",
																																												"616"	=>	"Poland",
																																												"630"	=>	"Puerto Rico",
																																												"620"	=>	"Portugal",
																																												"634"	=>	"Qatar",
																																												"642"	=>	"Romania",
																																												"643"	=>	"Russia",
																																												"682"	=>	"Saudi Arabia",
																																												"752"	=>	"Sweden",
																																												"702"	=>	"Singapore",
																																												"703"	=>	"Slovak Republic",
																																												"705"	=>	"Slovenia",
																																												"222"	=>	"ElSalvador",
																																												"760"	=>	"Syria",
																																												"764"	=>	"Thailand",
																																												"788"	=>	"Tunisia",
																																												"792"	=>	"Turkey",
																																												"780"	=>	"Trinidad and Tobago",
																																												"158"	=>	"Taiwan",
																																												"804"	=>	"Ukraine",
																																												"784"	=>	"United Arab Emirates",
																																												"858"	=>	"Uruguay",
																																												"860"	=>	"Uzbekistan",
																																												"862"	=>	"Venezuela",
																																												"704"	=>	"Vietnam",
																																												"887"	=>	"Yemen",
																																												"710"	=>	"South Africa",
																																												"716"	=>	"Zimbabwe"
																																);
							                                        if($this->config["ARIES"]["status"])
                                                                                                {
                                                                                                        unset($countryList["5000"]);
                                                                                                        unset($countryList["5001"]);
                                                                                                        $countryList["36"] = "Australia";
                                                                                                        $countryList["124"] = "Canada";
                                                                                                }
				
								}
												asort($countryList);
												return $countryList;
								}

								function getTimeZones()
								{
												$timeZones=array(	"0"	=>	"Afghanistan",
																																"1"	=>	"Albania",
																																"2"	=>	"Algeria",
																																"3"	=>	"American-Samoa",
																																"4"	=>	"Andorra",
																																"5"	=>	"Angola",
																																"6"	=>	"Anguilla",
																																"7"	=>	"Antigua-And-Barbuda",
																																"8"	=>	"Argentina",
																																"9"	=>	"Armenia",
																																"10"	=>	"Aruba",
																																"11"	=>	"Australia-Lordhoweisland",
																																"12"	=>	"Australia-New-South-Wales-Capital-Territory-Victoria",
																																"13"	=>	"Australia-Northern-Territory",
																																"14"	=>	"Australia-Queensland",
																																"15"	=>	"Australia-South-Australia-And-Broken-Hill",
																																"16"	=>	"Australia-Tasmania",
																																"17"	=>	"Australia-Western",
																																"18"	=>	"Austria",
																																"19"	=>	"Azerbaijan",
																																"20"	=>	"Bahamas",
																																"21"	=>	"Bahrain",
																																"22"	=>	"Bangladesh",
																																"23"	=>	"Barbados",
																																"24"	=>	"Belarus",
																																"25"	=>	"Belgium",
																																"26"	=>	"Belize",
																																"27"	=>	"Benin",
																																"28"	=>	"Bermuda",
																																"29"	=>	"Bhutan",
																																"30"	=>	"Bolivia",
																																"31"	=>	"Bonaire",
																																"32"	=>	"Bosnia-Herzegovina",
																																"33"	=>	"Botswana",
																																"34"	=>	"Brazil-East-Including-All-Coast-And-Brasilia",
																																"35"	=>	"Brazil-Fernando-De-Noronha",
																																"36"	=>	"Brazil-Trinity-Of-Acre",
																																"37"	=>	"Brazil-West",
																																"38"	=>	"British-Virgin-Islands",
																																"39"	=>	"Brunei",
																																"40"	=>	"Bulgaria",
																																"41"	=>	"Burkina-Faso",
																																"42"	=>	"Burma",
																																"43"	=>	"Burundi",
																																"44"	=>	"Cambodia",
																																"45"	=>	"Cameroon",
																																"46"	=>	"Canada-Atlantic",
																																"47"	=>	"Canada-Central",
																																"48"	=>	"Canada-Eastern",
																																"49"	=>	"Canada-Mountain",
																																"50"	=>	"Canada-Newfoundland",
																																"51"	=>	"Canada-Pacific-And-Yukon",
																																"52"	=>	"Canada-Saskatchewan",
																																"53"	=>	"Cape-Verde",
																																"54"	=>	"Cayman-Islands",
																																"55"	=>	"Central-African-Republic",
																																"56"	=>	"Chad",
																																"57"	=>	"Chile",
																																"58"	=>	"Chile-Easter-Island",
																																"59"	=>	"China",
																																"60"	=>	"Christmas-Islands",
																																"61"	=>	"Cocos-Keeling-Islands",
																																"62"	=>	"Colombia",
																																"63"	=>	"Congo",
																																"64"	=>	"Cook-Islands",
																																"65"	=>	"Costa-Rica",
																																"66"	=>	"Cote-D-Ivoire",
																																"67"	=>	"Croatia",
																																"68"	=>	"Cuba",
																																"69"	=>	"Curacao",
																																"70"	=>	"Cyprus",
																																"71"	=>	"Czech-Republic",
																																"72"	=>	"Denmark",
																																"73"	=>	"Djibouti",
																																"74"	=>	"Dominica",
																																"75"	=>	"The-Dominican-Republic",
																																"76"	=>	"Ecuador",
																																"77"	=>	"Ecuador-Galapagos-Islands",
																																"78"	=>	"Egypt",
																																"79"	=>	"El-Salvador",
																																"80"	=>	"Equatorial-Guinea",
																																"81"	=>	"Eritrea",
																																"82"	=>	"Estonia",
																																"83"	=>	"Ethiopia",
																																"84"	=>	"Faroe-Islands",
																																"85"	=>	"Fiji",
																																"86"	=>	"Finland",
																																"87"	=>	"France",
																																"88"	=>	"French-Guiana",
																																"89"	=>	"French-Polynesia",
																																"90"	=>	"Gabon",
																																"91"	=>	"The-Gambia",
																																"92"	=>	"Georgia",
																																"93"	=>	"Germany",
																																"94"	=>	"Ghana",
																																"95"	=>	"Gibraltar",
																																"96"	=>	"Greece",
																																"97"	=>	"Greenland-Scorsbysund",
																																"98"	=>	"Greenland-Thule",
																																"99"	=>	"Grenada",
																																"100"	=>	"Guadeloupe",
																																"101"	=>	"Guam",
																																"102"	=>	"Guatemala",
																																"103"	=>	"Guinea-Bissau",
																																"104"	=>	"Guyana",
																																"105"	=>	"Haiti",
																																"106"	=>	"Hawaii",
																																"107"	=>	"Honduras",
																																"108"	=>	"Hong-Kong",
																																"109"	=>	"Hungary",
																																"110"	=>	"Iceland",
																																"111"	=>	"India",
																																"112"	=>	"Indonesia-Central",
																																"113"	=>	"Indonesia-East",
																																"114"	=>	"Indonesia-West",
																																"115"	=>	"Iran",
																																"116"	=>	"Iraq",
																																"117"	=>	"Ireland",
																																"118"	=>	"Israel",
																																"119"	=>	"Italy",
																																"120"	=>	"Jamaica",
																																"121"	=>	"Japan",
																																"122"	=>	"Johnston-Islands",
																																"123"	=>	"Jordan",
																																"124"	=>	"Juan-Fernandez-Islands",
																																"125"	=>	"Kazakhstan",
																																"126"	=>	"Kenya",
																																"127"	=>	"Kiribati",
																																"128"	=>	"Kuwait",
																																"129"	=>	"Kyrgyzstan",
																																"130"	=>	"Laos",
																																"131"	=>	"Latvia",
																																"132"	=>	"Lebanon",
																																"133"	=>	"Leeward-Islands",
																																"134"	=>	"Lesotho",
																																"135"	=>	"Liberia",
																																"136"	=>	"Libya",
																																"137"	=>	"Liechtenstein",
																																"138"	=>	"Lithuania",
																																"139"	=>	"Luxembourg",
																																"140"	=>	"Macao",
																																"141"	=>	"Macedonia",
																																"142"	=>	"Madagascar",
																																"143"	=>	"Malawi",
																																"144"	=>	"Malaysia",
																																"145"	=>	"Maldives",
																																"146"	=>	"Mali",
																																"147"	=>	"Malta",
																																"148"	=>	"Mariana-Islands",
																																"149"	=>	"Martinique",
																																"150"	=>	"Mauritania",
																																"151"	=>	"Mauritius",
																																"152"	=>	"Mayotte",
																																"153"	=>	"Mexico",
																																"154"	=>	"Mexico-Baj-N",
																																"155"	=>	"Mexico-Baj-S",
																																"156"	=>	"Midway-Islands",
																																"157"	=>	"Moldova",
																																"158"	=>	"Monaco",
																																"159"	=>	"Mongolia",
																																"160"	=>	"Montenegro",
																																"161"	=>	"Montserrat",
																																"162"	=>	"Morocco",
																																"163"	=>	"Mozambique",
																																"164"	=>	"Namibia",
																																"165"	=>	"Nauru",
																																"166"	=>	"Nepal",
																																"167"	=>	"The-Netherlands-Antilles",
																																"168"	=>	"The-Netherlands",
																																"169"	=>	"New-Caledonia",
																																"170"	=>	"New-Hebrides",
																																"171"	=>	"New-Zealand",
																																"172"	=>	"New-Zealand-Chatham-Island",
																																"173"	=>	"Nicaragua",
																																"174"	=>	"Niger",
																																"175"	=>	"Nigeria",
																																"176"	=>	"Niue-Islands",
																																"177"	=>	"Norfolk-Island",
																																"178"	=>	"North-Korea",
																																"179"	=>	"Norway",
																																"180"	=>	"Oman",
																																"181"	=>	"Pakistan",
																																"182"	=>	"Palau",
																																"183"	=>	"Panama",
																																"184"	=>	"Papua-New-Guinea",
																																"185"	=>	"Paraguay",
																																"186"	=>	"Peru",
																																"187"	=>	"Philippines",
																																"188"	=>	"Pitcairn-Island",
																																"189"	=>	"Poland",
																																"190"	=>	"Portugal-Azores",
																																"191"	=>	"Portugal-Madeira",
																																"192"	=>	"Puerto-Rico",
																																"193"	=>	"Qatar",
																																"194"	=>	"Reunion",
																																"195"	=>	"Romania",
																																"196"	=>	"Russia-Moscow",
																																"197"	=>	"Russian-Fed-Zone-1-Kaliningrad",
																																"198"	=>	"Russian-Fed-Zone-10-Magadan",
																																"199"	=>	"Russian-Fed-Zone-11-Petropavlovsk-Kamchatsky",
																																"200"	=>	"Russian-Fed-Zone-2-St-Petersburg",
																																"201"	=>	"Russian-Fed-Zone-3-Izhevsk",
																																"202"	=>	"Russian-Fed-Zone-4-Ekaterinburg",
																																"203"	=>	"Russian-Fed-Zone-5-Novosibirsk",
																																"204"	=>	"Russian-Fed-Zone-6-Krasnojarsk",
																																"205"	=>	"Russian-Fed-Zone-7-Irkutsk",
																																"206"	=>	"Russian-Fed-Zone-8-Yakatsk",
																																"207"	=>	"Russian-Fed-Zone-9-Vladivostok",
																																"208"	=>	"Rwanda",
																																"209"	=>	"Saint-Pierre-And-Miquelon",
																																"210"	=>	"San-Marino",
																																"211"	=>	"Sao-Tome-And-Principe",
																																"212"	=>	"Saudi-Arabia",
																																"213"	=>	"Senegal",
																																"214"	=>	"Serbia",
																																"215"	=>	"The-Seychelles",
																																"216"	=>	"Sierra-Leone",
																																"217"	=>	"Singapore",
																																"218"	=>	"Slovakia",
																																"219"	=>	"Slovenia",
																																"220"	=>	"Solomon-Islands",
																																"221"	=>	"Somalia",
																																"222"	=>	"South-Africa",
																																"223"	=>	"South-Georgia",
																																"224"	=>	"South-Korea",
																																"225"	=>	"Spain",
																																"226"	=>	"Spain-Canary-Islands",
																																"227"	=>	"Sri-Lanka",
																																"228"	=>	"St-Helena",
																																"229"	=>	"St-Kitts-Nevis",
																																"230"	=>	"St-Lucia",
																																"231"	=>	"St-Vincent-And-The-Grenadines",
																																"232"	=>	"Sudan",
																																"233"	=>	"Suriname",
																																"234"	=>	"Swaziland",
																																"235"	=>	"Sweden",
																																"236"	=>	"Switzerland",
																																"237"	=>	"Syria",
																																"238"	=>	"Tahiti",
																																"239"	=>	"Taiwan",
																																"240"	=>	"Tajikistan",
																																"241"	=>	"Tanzania",
																																"242"	=>	"Thailand",
																																"243"	=>	"Togo",
																																"244"	=>	"Tonga",
																																"245"	=>	"Trinidad-And-Tobago",
																																"246"	=>	"Tunisia",
																																"247"	=>	"Turkey",
																																"248"	=>	"Turkmenistan",
																																"249"	=>	"Turks-And-Caicos-Islands",
																																"250"	=>	"Tuvalu",
																																"251"	=>	"Uganda",
																																"252"	=>	"Ukraine",
																																"253"	=>	"Ukraine-Simferopol",
																																"254"	=>	"United-Arab-Emirates",
																																"255"	=>	"United-Kingdom",
																																"256"	=>	"Uruguay",
																																"257"	=>	"US-Virgin-Islands",
																																"258"	=>	"USA-Alaska",
																																"259"	=>	"USA-Aleutian-Islands",
																																"260"	=>	"USA-Arizona",
																																"261"	=>	"USA-Central",
																																"262"	=>	"USA-Eastern",
																																"263"	=>	"USA-Indiana",
																																"264"	=>	"USA-Mountain",
																																"265"	=>	"USA-Pacific",
																																"266"	=>	"Uzbekistan",
																																"267"	=>	"Vanuatu",
																																"268"	=>	"Vatican-City",
																																"269"	=>	"Venezuela",
																																"270"	=>	"Vietnam",
																																"271"	=>	"Wake-Islands",
																																"272"	=>	"Wallis-And-Futana-Islands",
																																"273"	=>	"Western-Samoa",
																																"274"	=>	"Windward-Islands",
																																"275"	=>	"Yemen",
																																"276"	=>	"Zaire-Kasai",
																																"277"	=>	"Zaire-Kinshasa",
																																"278"	=>	"Zambia",
																																"279"	=>	"Zimbabwe"
																				);
												return $timeZones;
								}
	}

/**
* Class to provide IPv4 calculations
*
* PHP versions 4 and 5
*
* LICENSE: This source file is subject to version 3.01 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license/3_01.txt.  If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @category   Net
* @package    Net_IPv4
* @author     Eric Kilfoil <edk@ypass.net>
* @author     Marco Kaiser <bate@php.net>
* @author     Florian Anderiasch <fa@php.net>
* @copyright  1997-2005 The PHP Group
* @license    http://www.php.net/license/3_01.txt  PHP License 3.01
* @version    CVS: $Id: IPv4.php,v 1.11 2005/11/29 12:56:35 fa Exp $
* @link       http://pear.php.net/package/Net_IPv4
*
* The above license applies to the following class being extracted from Net_IPv4 package
*/

class ipAddress {
									private $ip = "";
									private $bitmask = false;
									private $netmask = "";
									private $network = "";
									private $broadcast = "";
									private $long = 0;

								/****************************
									* ipaddress related functions
									*/

								function __construct($ip,$netmask='0')
								{
												$this->parseAddress($ip.'/'.$netmask);
									}

									function ipStruct()
									{
													return array(   'ip' => $this->ip,
																													'bitmask' => $this->bitmask,
																													'netmask' => $this->netmask,
																													'network' => $this->network,
																													'broadcast' => $this->broadcast,
																													'long' => $this->long);
									}

									function validateIP($ip)
									{
													if ($ip == long2ip(ip2long($ip))) {
																	return true;
													} else {
																	return false;
													}
									}

									function check_ip($ip)
									{
													return $this->validateIP($ip);
									}

									function validateNetmask($netmask)
									{
													if (! in_array($netmask, $GLOBALS['Net_IPv4_Netmask_Map'])) {
																	return false;
													}
													return true;
									}

									function parseAddress($address)
									{
													if (strchr($address, "/")) {
																	$parts = explode("/", $address);
																	if (! $this->validateIP($parts[0])) {
																					$this->errorString = "invalid IP address";
																				return false;
																	}
																	$this->ip = $parts[0];
																	if (eregi("^([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$", $parts[1], $regs)) {
																					$this->netmask = hexdec($regs[1]) . "." .  hexdec($regs[2]) . "." .
																									hexdec($regs[3]) . "." .  hexdec($regs[4]);
																	} else if (strchr($parts[1], ".")) {
																					if (! $this->validateNetmask($parts[1])) {
																									$this->errorString = "invalid netmask value";
																								return false;
																					}
																					$this->netmask = $parts[1];
																	} else if ($parts[1] >= 0 && $parts[1] <= 32) {
																					$this->bitmask = $parts[1];
																	} else {
																					$this->errorString = "invalid netmask value";
																				return false;
																	}
																	$this->calculate();
																	return $this;
													} else if ($this->validateIP($address)) {
																	$this->ip = $address;
																	return $this;
													} else {
																	$this->errorString = "invalid IP address";
																return false;
													}

									}

									function calculate()
									{
													$validNM = $GLOBALS['Net_IPv4_Netmask_Map'];

													if (! is_a($this, "ipAddress")) {
																	$this->errorString = "cannot calculate on uninstantiated ipAddress class";
																return false;
													}

													if (strlen($this->ip)) {
																	if (! $this->validateIP($this->ip)) {
																					$this->errorString = "invalid IP address";
																				return false;
																	}
																	$this->long = $this->ip2double($this->ip);
													} else if (is_numeric($this->long)) {
																	$this->ip = long2ip($this->long);
													} else {
																$this->errorString = "ip address not specified";
															return false;
													}

													if (strlen($this->bitmask)) {
																	$this->netmask = $validNM[$this->bitmask];
													} else if (strlen($this->netmask)) {
																	$validNM_rev = array_flip($validNM);
																	$this->bitmask = $validNM_rev[$this->netmask];
													} else {
																	$this->errorString = "netmask or bitmask are required for calculation";
																return false;
													}
													$this->network = long2ip(ip2long($this->ip) & ip2long($this->netmask));
													$this->broadcast = long2ip(ip2long($this->ip) |
																					(ip2long($this->netmask) ^ ip2long("255.255.255.255")));
													return true;
									}

									function getNetmask($length)
									{
													if (($ipobj = $this->parseAddress("0.0.0.0/" . $length))) {
																	$mask = $ipobj->netmask;
																	unset($ipobj);
																	return $mask;
													}
													return false;
									}

									function getNetLength($netmask)
									{
													if (($ipobj = $this->parseAddress("0.0.0.0/" . $netmask))) {
																	$bitmask = $ipobj->bitmask;
																	unset($ipobj);
																	return $bitmask;
													}
													return false;
									}

									function getSubnet($ip, $netmask)
									{
													if (($ipobj = $this->parseAddress($ip . "/" . $netmask))) {
																	$net = $ipobj->network;
																	unset($ipobj);
																	return $net;
													}
													return false;
									}

									function inSameSubnet($ip1, $ip2)
									{
													if ($ip1->network == $ip2->network &&
																					$ip1->bitmask == $ip2->bitmask) {
																					return true;
													}
													return false;
									}

									function atoh($addr)
									{
													if (!$this->validateIP($addr)) {
																	return false;
													}
													$ap = explode(".", $addr);
													return sprintf("%02x%02x%02x%02x", $ap[0], $ap[1], $ap[2], $ap[3]);
									}

									function htoa($addr)
									{
													if (eregi("^([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$",
																									$addr, $regs)) {
																	return hexdec($regs[1]) . "." .  hexdec($regs[2]) . "." .
																								hexdec($regs[3]) . "." .  hexdec($regs[4]);
													}
													return false;
									}

									function ip2double($ip)
									{
													return (double)(sprintf("%u", ip2long($ip)));
									}

									function ipInNetwork($ip, $network)
									{
													if (! is_object($network) || strcasecmp(get_class($network), 'ipAddress') <> 0) {
																	$network = $this->parseAddress($network);
													}

													$net = $this->ip2double($network->network);
													$bcast = $this->ip2double($network->broadcast);
													$ip = $this->ip2double($ip);
													unset($network);
													if ($ip >= $net && $ip <= $bcast) {
																	return true;
													}
													return false;
									}

									function ipInPool($poolStart,$poolEnd)
									{
													if (! is_object($poolStart) || strcasecmp(get_class($poolStart), 'ipAddress') <> 0) {
																	$poolStart = ipAddress::parseAddress($poolStart);
													}
													if (! is_object($poolEnd) || strcasecmp(get_class($poolEnd), 'ipAddress') <> 0) {
																	$poolEnd = ipAddress::parseAddress($poolEnd);
													}

													$pS = $this->ip2double($poolStart->ip);
													$pE = $this->ip2double($poolEnd->ip);
													$ip = $this->ip2double($this->ip);
													unset($poolStart);
													unset($poolEnd);
													if ($ip >= $pS && $ip <= $pE) {
																	return true;
													}
													return false;
									}

									function ipLT($pool)
									{
													if (! is_object($pool) || strcasecmp(get_class($pool), 'ipAddress') <> 0) {
																	$pool = ipAddress::parseAddress($pool);
													}

													$pS = $this->ip2double($this->ip);
													$pE = $this->ip2double($pool->ip);
													if ($pS < $pE) {
																	return true;
													}
													return false;
									}

								function __destructor()
								{
												$this->template->clear_all_cache();
								}
				}


	?>
