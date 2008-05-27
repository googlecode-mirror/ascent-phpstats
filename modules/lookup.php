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
		global $_CONFIG,$_POST,$_GET,$system;
		require_once './inc/base.inc.php';
		
		if(($_SERVER["REQUEST_METHOD"]=="POST" AND isset($_POST) AND isset($_POST['c_user'])) OR isset($_GET['char'])){
			if(isset($_GET['char'])) $_POST['c_user']=$_GET['char'];
			$tpl->setParam('c_user',$_POST['c_user']);
			if(strlen($_POST['c_user'])<2){
				$tpl->setParam('c_msg','<center>Error:</center><br />'.'Account name... INVALID!');
				return;}
			$c_sql_user=mysql_escape_string($_POST['c_user']);
			$login_link=$system->mysql_login();
			$char_link=$system->mysql_connect();
			$sq2=array();
			if(Net_CheckIP::check_ip($_POST['c_user'])){
				$dr1=array();
				$dr1[2]=$_POST['c_user'];
				$sq2[]=mysql_query("SELECT `accounts`.`acct`, `accounts`.`login`, `accounts`.`lastip` FROM `accounts` WHERE `accounts`.`lastip` =  '{$dr1[2]}'",$login_link);
			}elseif(substr($_POST['c_user'],0,1)=="/"){
				$acc=substr($_POST['c_user'],1,strlen($_POST['c_user'])-1);
				$sq=mysql_query("SELECT `accounts`.`acct`, `accounts`.`login`, `accounts`.`lastip` FROM `accounts` WHERE `accounts`.`login` =  '".mysql_escape_string($acc)."'",$login_link);
				if(mysql_num_rows($sq)==0){
					$tpl->setParam('c_msg',"<center>Error:</center><br />"."account not exist");
					mysql_close($login_link);
					mysql_close($char_link);
					return;}
				$dr=mysql_fetch_array($sq);
				$sq2[]=mysql_query("SELECT `accounts`.`acct`, `accounts`.`login`, `accounts`.`lastip` FROM `accounts` WHERE `accounts`.`lastip` =  '{$dr[2]}'",$login_link);
			}else{
				$sq=mysql_query("SELECT `characters`.`acct`, `characters`.`guid`, `characters`.`name` FROM `characters` WHERE `characters`.`name` =  '{$c_sql_user}'",$char_link);
				if(mysql_num_rows($sq)==0){
					$tpl->setParam('c_msg',"<center>Error:</center><br />"."Char not exist");
					mysql_close($login_link);
					return;
				}elseif(mysql_num_rows($sq)==1){
					$dr=mysql_fetch_array($sq);
					$sq1=mysql_query("SELECT `accounts`.`acct`, `accounts`.`login`, `accounts`.`lastip` FROM `accounts` WHERE `accounts`.`acct` =  {$dr[0]}",$login_link);
					$dr1=mysql_fetch_array($sq1);
					$sq2[]=mysql_query("SELECT `accounts`.`acct`, `accounts`.`login`, `accounts`.`lastip` FROM `accounts` WHERE `accounts`.`lastip` =  '{$dr1[2]}'",$login_link);
				}elseif(mysql_num_rows($sq)>1){
					while ($dr = @mysql_fetch_array($sq)){
						$sq1=mysql_query("SELECT `accounts`.`acct`, `accounts`.`login`, `accounts`.`lastip` FROM `accounts` WHERE `accounts`.`acct` =  {$dr[0]}",$login_link);
						$dr1=mysql_fetch_array($sq1);
						$sq2[]=mysql_query("SELECT `accounts`.`acct`, `accounts`.`login`, `accounts`.`lastip` FROM `accounts` WHERE `accounts`.`lastip` =  '{$dr1[2]}'",$login_link);
					}
				}
			}
			
			$data=array();
			foreach($sq2 as $sql){
				while ($dr2 = @mysql_fetch_array($sql))
				{
					$data["{$dr2['login']} ({$dr2['acct']})"]=array();
					$sq3=mysql_query("SELECT `characters`.`name`, `characters`.`level`, `characters`.`race`, `characters`.`class`, `characters`.`gender` FROM `characters` WHERE `characters`.`acct` =  '{$dr2[0]}' ORDER BY `characters`.`level` DESC",$char_link);
					while ($dr3 = @mysql_fetch_array($sq3)){
						$dr3['isonline']=$system->is_online($dr3['name']);
						$data["{$dr2['login']} ({$dr2['acct']})"][]=$dr3;
					}
					@mysql_free_result($sq3);
				}
			}
			foreach($sq2 as $sql) @mysql_free_result($sql);
			@mysql_free_result($sq1);
			@mysql_free_result($sq);
			mysql_close($login_link);
			mysql_close($char_link);
			if(Net_CheckIP::check_ip($_POST['c_user']))
			$msg="Done! for ip {$_POST['c_user']}<br/>\n ";
			elseif(substr($_POST['c_user'],0,1)=="/")
			$msg="Done! for ip {$dr[2]} owner (acc {$acc})<br/>\n ";
			else
			$msg="Done! for ip {$dr1[2]} owner {$_POST['c_user']} (acc {$dr1[1]})<br/>\n ";

			foreach($data as $acc => $chars){
				$msg.="<hr/><h2>Account: {$acc}</h2>\n";
				foreach($chars as $char){
					$msg.="<img src=\"icon/class/{$char['class']}.gif\" alt=\"{$base_class[$char['class']]}\" />&nbsp;<img src=\"icon/race/{$char['race']}-{$char['gender']}.gif\" alt=\"{$base_race[$char['race']]}\" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$char['name']} ({$char['level']}) ";
					if($char['isonline']) $msg.="Online"; else $msg.="Offline";
					$msg.="<br/><br/>\n";
				}
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