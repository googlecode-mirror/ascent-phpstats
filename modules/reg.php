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
		return "";
	}
	function getdata(&$tpl){
		global $Cache,$_CONFIG,$count_acc,$count_gm;
		if(@$_GET['do']!='reg')
		{
			$tpl->setParam("FormReg_msg","");
			$tpl->setParam('FormReg_user',"");
			$tpl->setParam('FormReg_password',"");
			$tpl->setParam('FormReg_email',"");
		}else{
			$login_link=@mysql_connect($_CONFIG['MySQL_login_host'],$_CONFIG['MySQL_login_user'],$_CONFIG['MySQL_login_password'],true) or trigger_error("MySQL Err<br /> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
			if(!@mysql_select_db($_CONFIG['MySQL_login_db'],$login_link))
			 trigger_error("MySQL Err<br /> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
			$ip = getenv('REMOTE_ADDR');
			$isLastIP = mysql_result(mysql_query("SELECT count(`lastip`) FROM `accounts`  WHERE `lastip`='$ip'",$login_link),0);
			if(!eregi("^[a-z,A-Z]", @$_POST['reg_password']) OR !eregi("^[a-z,A-Z]", @$_POST['reg_name']) OR $isLastIP>=$_CONFIG['max_acc_per_ip'] OR (strlen(@$_POST['reg_password'])<6 OR strlen(@$_POST['reg_password'])>32) OR (strlen(@$_POST['reg_name'])<4 OR strlen(@$_POST['reg_name'])>16) OR !Net_CheckIP::check_ip($ip) OR !string_isEmail(@$_POST['reg_email']))
			{
				$body="<center>Error:</center><br />";
				if(!eregi("^[a-z,A-Z]", @$_POST['reg_name'])){$body.="- Login must contain <b>ONLY</b> low or upper letters<br />";}
				if(!eregi("^[a-z,A-Z]", @$_POST['reg_password'])){$body.="- Password must contain <b>ONLY</b> low or upper letters<br />";}
				if(strlen(@$_POST['reg_password'])<6 OR strlen(@$_POST['reg_password'])>32){$body.="- Password must be 6 letters minimum<br />";}
				if(strlen(@$_POST['reg_name'])<4 OR strlen(@$_POST['reg_name'])>16){$body.="- Login must contain from 4 to 16 letters<br />";}
				if($isLastIP>=$_CONFIG['max_acc_per_ip']){$body.="- There is only ".$_CONFIG['max_acc_per_ip']." account per one ip address ({$isLastIP})<br />";}
				if(!Net_CheckIP::check_ip($ip)){$body.="- you have invalid ip";}
				if(!string_isEmail(@$_POST['reg_email'])){$body.="- you have invalid email";}
				$tpl->setParam("FormReg_msg",$body);
				$tpl->setParam('FormReg_user',@$_POST['reg_name']);
				$tpl->setParam('FormReg_password',"");
				$tpl->setParam('FormReg_email',@$_POST['reg_email']);
			}else{
				$ts=CheckPassword($_POST['reg_name'],$_POST['reg_password']);
				if(!$ts[0]){
					$tpl->setParam("FormReg_msg","<center>Error:</center><br />".$ts[1]);
					$tpl->setParam('FormReg_user',$_POST['reg_name']);
					$tpl->setParam('FormReg_password',"");
					$tpl->setParam('FormReg_email',$_POST['reg_email']);
				}else{
					$username = htmlspecialchars(trim($_POST['reg_name']));
					$passw = trim($_POST['reg_password']);
					$email = htmlspecialchars(trim($_POST['reg_email']));
					if(@mysql_query("INSERT INTO `accounts` (`login`,`password`,`encrypted_password`,`lastip`,`email`,`flags`) VALUES ('$username','$passw','".SHA1(strtoupper($username).':'.strtoupper($passw))."','$ip','$email','8')",$login_link))
					{
						$Cache->open("./Cache/ch_pass_lock",NULL);
						$Cache->destroy($_POST['reg_name']);
						$Cache->open("./Cache/MySQL",NULL);
						$Cache->destroy('account');
						extract($Cache->c_get("MySQL","account",array('_CONFIG'=>$_CONFIG,'login_link'=>$login_link)),EXTR_OVERWRITE);
						$tpl->setParam("FormReg_msg","Account '{$username}' Created<br />\n Account active after ".$_CONFIG['reg_acc_active']."min");
						$tpl->setParam('FormReg_user',"");
						$tpl->setParam('FormReg_password',"");
						$tpl->setParam('FormReg_email',"");
					}else{
						$tpl->setParam("FormReg_msg","Account '{$username}' Failed");
						$tpl->setParam('FormReg_user',@$_POST['reg_name']);
						$tpl->setParam('FormReg_password',"");
						$tpl->setParam('FormReg_email',@$_POST['reg_email']);
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