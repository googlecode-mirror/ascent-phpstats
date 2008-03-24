<?php 
class module_lookup extends module_obj
{
	function ismenu(){
		return 0;
	}
	function isload(){
		global $is_admin;
		if(!$is_admin) return 0;

		return 1;
	}
	function getname(){
		return "LookupIP";
	}
	function getlink(){
		return "lookup";
	}
	function getdata(&$tpl){
		global $_CONFIG,$_POST,$_GET;
		require_once './inc/base.inc.php';
		
		if(($_SERVER["REQUEST_METHOD"]=="POST" AND isset($_POST) AND isset($_POST['c_user'])) OR isset($_GET['char'])){
			if(isset($_GET['char'])) $_POST['c_user']=$_GET['char'];
			$tpl->setParam('c_user',$_POST['c_user']);
			if(strlen($_POST['c_user'])<2){
				$tpl->setParam('c_msg','<center>Error:</center><br />'.'Account name... INVALID!');
				return;}
			$c_sql_user=mysql_escape_string($_POST['c_user']);
			$login_link=@mysql_connect($_CONFIG['MySQL_login_host'],$_CONFIG['MySQL_login_user'],$_CONFIG['MySQL_login_password'],true) or trigger_error("MySQL Err<br /> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
			if(!@mysql_select_db($_CONFIG['MySQL_login_db'],$login_link))
			 trigger_error("MySQL Err<br /> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
			$char_link=@mysql_connect($_CONFIG["MySQL_char_host"],$_CONFIG["MySQL_char_user"],$_CONFIG["MySQL_char_password"],true) or trigger_error("MySQL Err<br /> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
			if(!@mysql_select_db($_CONFIG["MySQL_char_db"],$char_link)) trigger_error("MySQL Err<br /> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
			if(!Net_CheckIP::check_ip($_POST['c_user'])){
				$sq=mysql_query("SELECT `characters`.`acct`, `characters`.`guid`, `characters`.`name` FROM `characters` WHERE `characters`.`name` =  '{$c_sql_user}'",$char_link);
				if(mysql_num_rows($sq)==0){
					$tpl->setParam('c_msg',"<center>Error:</center><br />"."Char not exist");
					mysql_close($login_link);
					return;}
				$dr=mysql_fetch_array($sq);
				$sq1=mysql_query("SELECT `accounts`.`acct`, `accounts`.`login`, `accounts`.`lastip` FROM `accounts` WHERE `accounts`.`acct` =  {$dr[0]}",$login_link);
				$dr1=mysql_fetch_array($sq1);	
			}else{
				$dr1=array();
				$dr1[2]=$_POST['c_user'];
			}
			$sq2=mysql_query("SELECT `accounts`.`acct`, `accounts`.`login`, `accounts`.`lastip` FROM `accounts` WHERE `accounts`.`lastip` =  '{$dr1[2]}'",$login_link);
			$data=array();
			while ($dr2 = @mysql_fetch_array($sq2))
			{
				$data["{$dr2['login']} ({$dr2['acct']})"]=array();
				$sq3=mysql_query("SELECT `characters`.`name`, `characters`.`level`, `characters`.`race`, `characters`.`class`, `characters`.`gender` FROM `characters` WHERE `characters`.`acct` =  '{$dr2[0]}' ORDER BY `characters`.`level` DESC",$char_link);
				while ($dr3 = @mysql_fetch_array($sq3))
					$data["{$dr2['login']} ({$dr2['acct']})"][]=$dr3;
				@mysql_free_result($sq3);
			}
			@mysql_free_result($sq2);
			@mysql_free_result($sq1);
			@mysql_free_result($sq);
			mysql_close($login_link);
			mysql_close($char_link);
			if(!Net_CheckIP::check_ip($_POST['c_user']))
			$msg="Done! for ip {$dr1[2]} owner {$_POST['c_user']} (acc {$dr1[1]})<br/>\n ";
			else
			$msg="Done! for ip {$_POST['c_user']}<br/>\n ";
			foreach($data as $acc => $chars){
				$msg.="<hr/><h2>Account: {$acc}</h2>\n";
				foreach($chars as $char)$msg.="<img src=\"icon/class/{$char['class']}.gif\" alt=\"{$base_class[$char['class']]}\" />&nbsp;<img src=\"icon/race/{$char['race']}-{$char['gender']}.gif\" alt=\"{$base_race[$char['race']]}\" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$char['name']} ({$char['level']})<br/><br/>\n";
			}
			$tpl->setParam('c_msg',$msg);
		}else{
			$tpl->setParam('c_msg','');
			$tpl->setParam('c_user','');
		}
	}
	function gettplfile(){
		return "lookup";
	}
}
?>