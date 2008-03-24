<?php 
class module_toparena extends module_obj
{
	var $sql_select_1="SELECT `arenateams`.`name`, `arenateams`.`rating`, `arenateams`.`data`, `leader`.`name` AS `leader_name` FROM `arenateams` Inner Join `characters` AS `leader` ON `arenateams`.`leader` = `leader`.`guid` WHERE `arenateams`.`type` =  '0' AND `arenateams`.`data` NOT LIKE  '0 0 0 0' ORDER BY `arenateams`.`rating` DESC LIMIT 5";
	var $sql_select_2="SELECT `arenateams`.`name`, `arenateams`.`rating`, `arenateams`.`data`, `leader`.`name` AS `leader_name` FROM `arenateams` Inner Join `characters` AS `leader` ON `arenateams`.`leader` = `leader`.`guid` WHERE `arenateams`.`type` =  '1' AND `arenateams`.`data` NOT LIKE  '0 0 0 0' ORDER BY `arenateams`.`rating` DESC LIMIT 5";
	var $sql_select_3="SELECT `arenateams`.`name`, `arenateams`.`rating`, `arenateams`.`data`, `leader`.`name` AS `leader_name` FROM `arenateams` Inner Join `characters` AS `leader` ON `arenateams`.`leader` = `leader`.`guid` WHERE `arenateams`.`type` =  '2' AND `arenateams`.`data` NOT LIKE  '0 0 0 0' ORDER BY `arenateams`.`rating` DESC LIMIT 5";
	function module_toparena(){
		global $Cache;
		$Cache->c_add("MySQL","arena2v2",array($this,'c_cache_2v2'));
		$Cache->c_add("MySQL","arena3v3",array($this,'c_cache_3v3'));
		$Cache->c_add("MySQL","arena5v5",array($this,'c_cache_5v5'));
	}
	function ismenu(){
		return 1;
	}
	function isload(){
		//if(!is_admin(getenv('REMOTE_ADDR'))) return 0;

		return 1;
	}
	function getname(){
		return "Arena";
	}
	function getlink(){
		return "arena";
	}
	function c_cache_2v2($var){
		global $Cache;
		extract($var,EXTR_OVERWRITE);
		$db_info=array();
		$Cache->open("./Cache/MySQL",NULL);
		$db_info = $Cache->read("arena2v2");
		if(!empty($db_info))
			$db_info=unserialize($db_info);
		if(is_array($db_info)){
			$frt=$db_info;
			unset($db_info);
		}else{
			$char_link=@mysql_connect($_CONFIG["MySQL_char_host"],$_CONFIG["MySQL_char_user"],$_CONFIG["MySQL_char_password"],true) or trigger_error("MySQL Err<br /> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
			if(!@mysql_select_db($_CONFIG["MySQL_char_db"],$char_link)) trigger_error("MySQL Err<br /> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
			$res=mysql_query($this->sql_select_1,$char_link);
			$data=array();
			while ($char = mysql_fetch_array($res)){
				$data[]=$char;
			}
			@mysql_free_result($res);
			mysql_close($char_link);
			$frt=array("arena2v2"=>$data);
			$Cache->write("arena2v2",serialize($frt));
		}
		return $frt;
	}
	function c_cache_3v3($var){
		global $Cache;
		extract($var,EXTR_OVERWRITE);
		$db_info=array();
		$Cache->open("./Cache/MySQL",NULL);
		$db_info = $Cache->read("arena3v3");
		if(!empty($db_info))
			$db_info=unserialize($db_info);
		if(is_array($db_info)){
			$frt=$db_info;
			unset($db_info);
		}else{
			$char_link=@mysql_connect($_CONFIG["MySQL_char_host"],$_CONFIG["MySQL_char_user"],$_CONFIG["MySQL_char_password"],true) or trigger_error("MySQL Err<br /> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
			if(!@mysql_select_db($_CONFIG["MySQL_char_db"],$char_link)) trigger_error("MySQL Err<br /> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
			$res=mysql_query($this->sql_select_2,$char_link);
			$data=array();
			while ($char = mysql_fetch_array($res)){
				$data[]=$char;
			}
			@mysql_free_result($res);
			mysql_close($char_link);
			$frt=array("arena3v3"=>$data);
			$Cache->write("arena3v3",serialize($frt));
		}
		return $frt;
	}
	function c_cache_5v5($var){
		global $Cache;
		extract($var,EXTR_OVERWRITE);
		$db_info=array();
		$Cache->open("./Cache/MySQL",NULL);
		$db_info = $Cache->read("arena5v5");
		if(!empty($db_info))
			$db_info=unserialize($db_info);
		if(is_array($db_info)){
			$frt=$db_info;
			unset($db_info);
		}else{
			$char_link=@mysql_connect($_CONFIG["MySQL_char_host"],$_CONFIG["MySQL_char_user"],$_CONFIG["MySQL_char_password"],true) or trigger_error("MySQL Err<br /> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
			if(!@mysql_select_db($_CONFIG["MySQL_char_db"],$char_link)) trigger_error("MySQL Err<br /> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
			$res=mysql_query($this->sql_select_3,$char_link);
			$data=array();
			while ($char = mysql_fetch_array($res)){
				$data[]=$char;
			}
			@mysql_free_result($res);
			mysql_close($char_link);
			$frt=array("arena5v5"=>$data);
			$Cache->write("arena5v5",serialize($frt));
		}
		return $frt;
	}
	function getdata(&$tpl){
		global $_CONFIG,$Cache,$_GET;
		require_once './inc/base.inc.php';
		$tpl->setBlock('MOD_PAGE', 'a2v2');
		$tpl->setBlock('MOD_PAGE', 'a3v3');
		$tpl->setBlock('MOD_PAGE', 'a5v5');
		extract($Cache->c_get("MySQL","arena2v2",array('_CONFIG'=>$_CONFIG)),EXTR_OVERWRITE);
		extract($Cache->c_get("MySQL","arena3v3",array('_CONFIG'=>$_CONFIG)),EXTR_OVERWRITE);
		extract($Cache->c_get("MySQL","arena5v5",array('_CONFIG'=>$_CONFIG)),EXTR_OVERWRITE);
		foreach($arena2v2 as $rank => $char){
			$tpl->setParam('a2v2_rank',$rank+1);
			$tpl->setParam('a2v2_name',$char["name"]);
			$tpl->setParam('a2v2_leader',$char["leader_name"]);
			list($gamesplayedweek,$gameswonweek,$gamesplayedseason,$gameswonseason)=explode(" ",$char["data"]);
			$tpl->setParam('a2v2_win',$gameswonseason);
			$tpl->setParam('a2v2_lose',$gamesplayedseason-$gameswonseason);
			$tpl->setParam('a2v2_rating',$char["rating"]);
			
			$tpl->parseParam('a2v2', 'a2v2Dynamic', true);
		}
		$tpl->setParam('a2v2', $tpl->getParam('a2v2Dynamic'));
		foreach($arena3v3 as $rank => $char){
			$tpl->setParam('a3v3_rank',$rank+1);
			$tpl->setParam('a3v3_name',$char["name"]);
			$tpl->setParam('a3v3_leader',$char["leader_name"]);
			list($gamesplayedweek,$gameswonweek,$gamesplayedseason,$gameswonseason)=explode(" ",$char["data"]);
			$tpl->setParam('a3v3_win',$gameswonseason);
			$tpl->setParam('a3v3_lose',$gamesplayedseason-$gameswonseason);
			$tpl->setParam('a3v3_rating',$char["rating"]);
			
			$tpl->parseParam('a3v3', 'a3v3Dynamic', true);
		}
		$tpl->setParam('a3v3', $tpl->getParam('a3v3Dynamic'));
		foreach($arena5v5 as $rank => $char){
			$tpl->setParam('a5v5_rank',$rank+1);
			$tpl->setParam('a5v5_name',$char["name"]);
			$tpl->setParam('a5v5_leader',$char["leader_name"]);
			list($gamesplayedweek,$gameswonweek,$gamesplayedseason,$gameswonseason)=explode(" ",$char["data"]);
			$tpl->setParam('a5v5_win',$gameswonseason);
			$tpl->setParam('a5v5_lose',$gamesplayedseason-$gameswonseason);
			$tpl->setParam('a5v5_rating',$char["rating"]);
			
			$tpl->parseParam('a5v5', 'a5v5Dynamic', true);
		}
		$tpl->setParam('a5v5', $tpl->getParam('a5v5Dynamic'));
	}
	function gettplfile(){
		return "arena";
	}
}
?>