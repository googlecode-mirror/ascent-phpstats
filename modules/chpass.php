<?php 
class module_chpass extends module_obj
{
	function ismenu(){
		return 1;
	}
	function isload(){
		if (!extension_loaded('mysql')) return 0;

		return 1;
	}
	function getname(){
		return "Change Password";
	}
	function getlink(){
		return "cp";
	}
	function getdata(&$tpl){
		global $system,$_CONFIG,$_POST,$_GET;
		if($_SERVER["REQUEST_METHOD"]=="POST" AND isset($_POST) AND isset($_POST['c_user'])){
			$tpl->setParam('c_user',$_POST['c_user']);
			if(strlen($_POST['c_user'])<2){
				$tpl->setParam('c_msg','<center>Error:</center><br />'.'Account name... INVALID!');
				return;}
			if(strlen(@$_POST['c_password'])<6 OR strlen(@$_POST['c_password'])>32){
				$tpl->setParam('c_msg',"<center>Error:</center><br />"."Password must be 6 letters minimum");
				return;}
			if(strlen(@$_POST['c_repassword'])<6 OR strlen(@$_POST['c_repassword'])>32){
				$tpl->setParam('c_msg',"Re Password must be 6 letters minimum");
				return;}
			if(!eregi($system->valid_str, @$_POST['c_password'])){
				$tpl->setParam('c_msg',"<center>Error:</center><br />"."Password must contain <b>ONLY</b> low or upper letters");
				return;}
			if(!eregi($system->valid_str, @$_POST['c_repassword'])){
				$tpl->setParam('c_msg',"<center>Error:</center><br />"."Re Password must contain <b>ONLY</b> low or upper letters");
				return;}
			if(@$_POST['c_password'] != @$_POST['c_repassword']){
				$tpl->setParam('c_msg',"<center>Error:</center><br />"."Re-Password not equal Password");
				return;}
			if(strlen(@$_POST['c_oldpassword'])<2 OR strlen(@$_POST['c_oldpassword'])>16){
				$tpl->setParam('c_msg',"<center>Error:</center><br />"."Old-Password must be 2 letters minimum");
				return;}
			$ts=CheckPassword($_POST['c_user'],$_POST['c_password']);
			if(!$ts[0]){
				$tpl->setParam('c_msg',"<center>Error:</center><br />".$ts[1]);
				return;}
			$c_sql_user=mysql_escape_string($_POST['c_user']);
			$system->cache->open("./Cache/ch_pass_lock",NULL);
			$baninfo = $system->cache->read($_POST['c_user']);
			if(strlen($baninfo)>0){ //Invalid Password Or Name
				$tpl->setParam('c_msg','Block account change password:<br />'.$baninfo);
				return;
			}
			$sql_get_pass="SELECT `accounts`.`password` FROM `accounts` WHERE `accounts`.`login` =  '{$c_sql_user}' AND `accounts`.`gm` =  \"\"";
			//var_dump($sql_get_pass);
			$login_link=$system->mysql_login();
			$sq=mysql_query($sql_get_pass,$login_link);
			//var_dump($sql_get_pass);
			if(mysql_num_rows($sq)==0){
				//var_dump(mysql_num_rows($sq));
				$tpl->setParam('c_msg',"<center>Error:</center><br />"."Account not exist");
				$Cache->write($_POST['c_user'],"Account not exist or Invalid Password");
				mysql_close($login_link);
				return;}
			$db_oldpass=mysql_result($sq,0);
			if(strtolower($db_oldpass) != strtolower($_POST['c_oldpassword'])){
				$tpl->setParam('c_msg',"Invalid Password");
				$system->cache->write($_POST['c_user'],"Account not exist or Invalid Password");
				mysql_close($login_link);
				return;}
			mysql_query("UPDATE `accounts` set `password` = '".mysql_escape_string($_POST['c_password'])."',`encrypted_password`='".SHA1(strtoupper($_POST['c_user']).':'.strtoupper($_POST['c_password']))."' WHERE `login` = '{$c_sql_user}'",$login_link);
			$result=mysql_query("SELECT `accounts`.`login` FROM `accounts` WHERE `accounts`.`lastip` =  '".getenv('REMOTE_ADDR')."'",$login_link);
			$dt=array();
			while ($dr = mysql_fetch_array($result))$dt[]=$dr['login'];
			@mysql_free_result($result);
			$logtxt="CHANGE_PASS ACCOUNT:{$_POST['c_user']} | IP:".getenv('REMOTE_ADDR');
			if(count($dt)>0)$logtxt .= " | LAST_IP_ACCOUNTS:[".implode($dt,",")."]";			
			$system->log->log($logtxt);
			mysql_close($login_link);
			$tpl->setParam('c_msg','Done!');
		}else{
			$tpl->setParam('c_msg','');
			$tpl->setParam('c_user','');
		}
	}
	function gettplfile(){
		return "chpass";
	}
}
?>