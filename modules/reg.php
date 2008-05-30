<?php 
class module_reg extends module_obj
{
	function ismenu(){
		return 1;
	}
	function isload(){
		if (!extension_loaded('mysql')) return 0;

		return 1;
	}
	function getname(){
		return "Registration";
	}
	function getlink(){
		return "reg";
	}
	function make_CAPTCHA(&$tpl){
		global $system;
		$CAPTCHA = Text_CAPTCHA::factory('Image');
		$retval = $CAPTCHA->init(200, 80, null, array('font_size' => 24,'font_path' => './icon/','font_file' => 'verdana.TTF'));
		if(PEAR::isError($retval)){
			$tpl->setParam("FormReg_msg","Error generating CAPTCHA!");
			$tpl->setParam('FormReg_user',"");
			$tpl->setParam('FormReg_password',"");
			$tpl->setParam('FormReg_email',"");
			$tpl->setParam('FormReg_CAPTCHA',"");
			return false;
		}
		$png = $CAPTCHA->getCAPTCHAAsPNG();
		if (PEAR::isError($png)) {
			$tpl->setParam("FormReg_msg",'Error generating CAPTCHA!');
			$tpl->setParam('FormReg_user',"");
			$tpl->setParam('FormReg_password',"");
			$tpl->setParam('FormReg_email',"");
			$tpl->setParam('FormReg_CAPTCHA',"");
			return false;
		}
		$_SESSION['phrase'] = $CAPTCHA->getPhrase();
		file_put_contents($system->_cache."/sess_".md5(session_id()) . ".png", $png);
		return true;
	}
	function getdata(&$tpl){
		global $system,$_CONFIG,$count_acc,$count_gm,$is_admin;
		$FormReg_CAPTCHA="sess_".md5(session_id()) . '.png';
		$tpl->setParam("FormReg_msg","");
		if($_SERVER['REQUEST_METHOD'] != 'POST')
		{
			if(!$this->make_CAPTCHA($tpl)) return;
			$tpl->setParam('FormReg_user',"");
			$tpl->setParam('FormReg_password',"");
			$tpl->setParam('FormReg_email',"");
			$tpl->setParam('FormReg_CAPTCHA',"./Cache/" .$FormReg_CAPTCHA . "?" . time());
		}else{
			$login_link=$system->mysql_login();
			$ip = getenv('REMOTE_ADDR');
			$isLastIP = mysql_result(mysql_query("SELECT count(`lastip`) FROM `accounts`  WHERE `lastip`='$ip'",$login_link),0);
			if(!eregi($system->valid_str, @$_POST['reg_password']) OR !eregi($system->valid_str, @$_POST['reg_name']) OR ($isLastIP>=$_CONFIG['max_acc_per_ip'] and !$is_admin) OR (strlen(@$_POST['reg_password'])<6 OR strlen(@$_POST['reg_password'])>32) OR (strlen(@$_POST['reg_name'])<4 OR strlen(@$_POST['reg_name'])>16) OR !Net_CheckIP::check_ip($ip) OR !string_isEmail(@$_POST['reg_email']) OR @$_POST['phrase'] != @$_SESSION['phrase'])
			{
				$body="<center>Error:</center><br />";
				if(!eregi($system->valid_str, @$_POST['reg_name'])){$body.="- Login must contain <b>ONLY</b> low or upper letters<br />";}
				if(!eregi($system->valid_str, @$_POST['reg_password'])){$body.="- Password must contain <b>ONLY</b> low or upper letters<br />";}
				if(strlen(@$_POST['reg_password'])<6 OR strlen(@$_POST['reg_password'])>17){$body.="- Password must be 6 letters minimum<br />";}
				if(strlen(@$_POST['reg_name'])<4 OR strlen(@$_POST['reg_name'])>16){$body.="- Login must contain from 4 to 16 letters<br />";}
				if($isLastIP>=$_CONFIG['max_acc_per_ip'] and !$is_admin){$body.="- There is only ".$_CONFIG['max_acc_per_ip']." account per one ip address ({$isLastIP})<br />";}
				if(!Net_CheckIP::check_ip($ip)){$body.="- you have invalid ip<br />";}
				if(@$_POST['phrase'] != @$_SESSION['phrase']){$body.="- text of the image invalid<br />";}
				if(!string_isEmail(@$_POST['reg_email'])){$body.="- you have invalid email<br />";}
				$tpl->setParam("FormReg_msg",$body);
				$tpl->setParam('FormReg_user',@$_POST['reg_name']);
				$tpl->setParam('FormReg_password',"");
				$tpl->setParam('FormReg_email',@$_POST['reg_email']);
				if(!$this->make_CAPTCHA($tpl)) return;
				$tpl->setParam('FormReg_CAPTCHA',"./Cache/" .$FormReg_CAPTCHA . "?" . time());
			}else{
				$ts=CheckPassword($_POST['reg_name'],$_POST['reg_password']);
				if(!$ts[0]){
					$tpl->setParam("FormReg_msg","<center>Error:</center><br />".$ts[1]);
					$tpl->setParam('FormReg_user',$_POST['reg_name']);
					$tpl->setParam('FormReg_password',"");
					$tpl->setParam('FormReg_email',$_POST['reg_email']);
					if(!$this->make_CAPTCHA($tpl)) return;
					$tpl->setParam('FormReg_CAPTCHA',"./Cache/" .$FormReg_CAPTCHA . "?" . time());
				}else{
					$username = mysql_escape_string(trim($_POST['reg_name']));
					$passw = trim($_POST['reg_password']);
					$email = mysql_escape_string(trim($_POST['reg_email']));
					if(!$this->make_CAPTCHA($tpl)) return;
					if(@mysql_query("INSERT INTO `accounts` (`login`,`password`,`encrypted_password`,`lastip`,`email`,`flags`) VALUES ('$username','$passw','".SHA1(strtoupper($username).':'.strtoupper($passw))."','$ip','$email','8')",$login_link))
					{
						$system->cache->open("./Cache/ch_pass_lock",NULL);
						$system->cache->destroy($_POST['reg_name']);
						$system->cache->open("./Cache/MySQL",NULL);
						$system->cache->destroy('account');
						extract($system->cache->c_get("MySQL","account",array('_CONFIG'=>$_CONFIG,'login_link'=>$login_link)),EXTR_OVERWRITE);
						$tpl->setParam("FormReg_msg","Account '{$username}' Created<br />\n Account active after ".$_CONFIG['reg_acc_active']."min");
						$tpl->setParam('FormReg_user',"");
						$tpl->setParam('FormReg_password',"");
						$tpl->setParam('FormReg_email',"");
						$tpl->setParam('FormReg_CAPTCHA',"./Cache/" .$FormReg_CAPTCHA . "?" . time());
					}else{
						$tpl->setParam("FormReg_msg","Account '{$username}' Failed");
						$tpl->setParam('FormReg_user',@$_POST['reg_name']);
						$tpl->setParam('FormReg_password',"");
						$tpl->setParam('FormReg_email',@$_POST['reg_email']);
						$tpl->setParam('FormReg_CAPTCHA',"./Cache/" .$FormReg_CAPTCHA . "?" . time());
					}
				}
			}	
			mysql_close($login_link);
		}
		
       		
	}
	function gettplfile(){
		return "reg";
	}
}
?>