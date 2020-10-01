<?php
    function checkSessionLink()
	{
		if(file_exists('/tmp/sessionid')) {
			return true;
		}
		else {
			return false;
		}
	}

	function checkSessionExpired()
	{
		if (checkSessionLink()) {
			if ((time()-@filemtime('/tmp/sessionid'))>300)
				return true;
			else 
				return false;
		}
		else
			return true;
	}

	$passStr = conf_get("system:basicSettings:adminPasswd");
    	$str = explode(' ',$passStr);
	$str[1] = str_replace('\:',':',$str[1]);
	//$str[1] = str_replace('\\ ',' ',$str[1]);
	//$str[1] = str_replace('\\\\','\\',$str[1]);
    //$str[1] = 'password';
	
    if ($_REQUEST['username'] == 'admin' && htmlentities($_REQUEST['password']) == htmlentities($str[1])) {
    	if (checkSessionExpired()===false) {
    		echo 'sessionexists';
    	}
		else {
			session_start();
                        $_SESSION['username']=$_REQUEST['username'];
			$fp = fopen('/tmp/sessionid', 'w');
			fwrite($fp, session_id().','.$_SERVER['REMOTE_ADDR']);
			fclose($fp);
			echo 'loginok';
		}
	}
	else {
		header('location:index.php');
	}
?>
