<?php 
class module_unstack extends module_obj
{
	var $cd=7200;
	function ismenu(){
		return 1;
	}
	function isload(){
		if (!extension_loaded('mysql')) return 0;

		return 1;
	}
	function getname(){
		return "unstuck";
	}
	function getlink(){
		return "uns";
	}
	function makeunstuck($sql_link,$guid){
		global $_GET,$is_admin;
		mysql_query("update characters set positionX=bindpositionX,positionY=bindpositionY,positionZ=bindpositionZ,mapId=bindmapId,zoneId=bindzoneId,deathstate=0,current_hp=100,taxi_path=0,taxi_lastnode=0,taxi_mountid=0,auras=\"\" where guid={$guid};",$sql_link);
		$result = mysql_query("SELECT player_flags FROM characters WHERE guid = {$guid}",$sql_link);
		$row = mysql_fetch_array($result);
		$player_flags = $row[0];
		if ($player_flags & 0x10){
			$player_flags &= ~0x10;
			mysql_query("UPDATE characters SET player_flags = $player_flags WHERE guid = {$guid}",$sql_link);
			mysql_query("DELETE FROM corpses WHERE guid = {$guid}",$sql_link);
		}
	}
	function getdata(&$tpl){
		global $system,$_CONFIG,$_POST,$_GET,$is_admin;
		
		if($_SERVER["REQUEST_METHOD"]=="POST" AND isset($_POST) AND isset($_POST['c_user'])){
			$tpl->setParam('c_user',$_POST['c_user']);
			if(strlen($_POST['c_user'])<2){
				$tpl->setParam('c_msg','<center>Error:</center><br />'.'Account name... INVALID!');
				return;}
			if(strlen(@$_POST['c_password'])<6 OR strlen(@$_POST['c_password'])>32){
				$tpl->setParam('c_msg',"<center>Error:</center><br />"."Password must be 6 letters minimum");
				return;}
			if(!eregi("^[a-z,A-Z]", @$_POST['c_password'])){
				$tpl->setParam('c_msg',"<center>Error:</center><br />"."Password must contain <b>ONLY</b> low or upper letters");
				return;}
			if(!eregi("^[a-z,A-Z]", @$_POST['c_char'])){
				$tpl->setParam('c_msg',"<center>Error:</center><br />"."char must contain <b>ONLY</b> low or upper letters");
				return;}
			$c_sql_user=mysql_escape_string($_POST['c_user']);
			$sql_get_pass="SELECT `accounts`.`password`,`accounts`.`acct` FROM `accounts` WHERE `accounts`.`login` =  '{$c_sql_user}'";
			//var_dump($sql_get_pass);
			$login_link=@mysql_connect($_CONFIG['MySQL_login_host'],$_CONFIG['MySQL_login_user'],$_CONFIG['MySQL_login_password'],true) or trigger_error("MySQL Err<br /> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
			if(!@mysql_select_db($_CONFIG['MySQL_login_db'],$login_link))
			 trigger_error("MySQL Err<br /> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
			$sq=mysql_query($sql_get_pass,$login_link);
			//var_dump($sql_get_pass);
			if(mysql_num_rows($sq)==0){
				//var_dump(mysql_num_rows($sq));
				$tpl->setParam('c_msg',"<center>Error:</center><br />"."Account not exist");
				$system->ban(getenv('REMOTE_ADDR'),60,"Account not exist or Invalid Password, autoban 1m from module ".$this->getname());
				//$Cache->write($_POST['c_user'],"Account not exist or Invalid Password");
				mysql_close($login_link);
				return;}
			$system->cache->open("./Cache/cd_unstuck",NULL);
			$system->cache->gc($this->cd);
			$time=$system->cache->read($_POST['c_user']);
			if(!empty($time) and !$is_admin){
				$sd_m=round(($this->cd-(mktime()-$time))/60,0);
				$sd_h=floor($sd_m/60);
				$sd_m=$sd_m-$sd_h*60;
				if($sd_m<0)$sd_m=0;
				if($sd_h<0)$sd_h=0;
				if($sd_h>0)
				$tpl->setParam('c_msg',"<center>Error:</center><br />"."Coldown left {$sd_h} h {$sd_m} m");
				else
				$tpl->setParam('c_msg',"<center>Error:</center><br />"."Coldown left {$sd_m} m");
				mysql_close($login_link);
				return;}
			$dr=mysql_fetch_array($sq);
			$db_pass=$dr['password'];
			$acc_id=$dr['acct'];
			if(strtolower($db_pass) != strtolower($_POST['c_password'])){
				$tpl->setParam('c_msg',"Invalid Password");
				$system->ban(getenv('REMOTE_ADDR'),60,"Account not exist or Invalid Password, autoban 1m from module ".$this->getname());
				mysql_close($login_link);
				return;}
			@mysql_free_result($sq);
			mysql_close($login_link);
			$char_link=$system->mysql_connect();
			$sq1=mysql_query("SELECT `characters`.`guid`, `characters`.`online` FROM `characters` WHERE `characters`.`acct` = ".$acc_id." AND `characters`.`name` =  '".mysql_escape_string($_POST['c_char'])."'",$char_link);
			if(mysql_num_rows($sq1)==0){
				$tpl->setParam('c_msg',"<center>Error:</center><br />"."character not exist");
				mysql_close($char_link);
				return;}
			$dr1=mysql_fetch_array($sq1);
			if($dr1['online']>0){
				$tpl->setParam('c_msg',"<center>Error:</center><br />"."Account is online");
				mysql_close($char_link);
				return;}
			$this->makeunstuck($char_link,$dr1['guid']);
			@mysql_free_result($sq1);
			mysql_close($char_link);
			$system->cache->write($_POST['c_user'],mktime());
			$logtxt="UNSTUCK ACCOUNT:{$_POST['c_user']} | GUID:{$dr1['guid']} | CHAR:{$_POST['c_char']} | IP:".getenv('REMOTE_ADDR');			
			$system->log->log($logtxt);
			
			$tpl->setParam('c_msg','Done!');
		}else{
			$tpl->setParam('c_msg','');
			$tpl->setParam('c_user','');
		}
	}
	function gettplfile(){
		return "unstuck";
	}
}
?>