<?php 
class module_toparena extends module_obj
{
	var $top_limit=10;
	var $sql_select_1="SELECT `arenateams`.`name`, `arenateams`.`rating`, `arenateams`.`data`, `leader`.`name` AS `leader_name`, `arenateams`.`player_data1`AS `data_member1`, `arenateams`.`player_data2`AS `data_member2`, `arenateams`.`player_data3`AS `data_member3`, `arenateams`.`player_data4`AS `data_member4` FROM `arenateams` Inner Join `characters` AS `leader` ON `arenateams`.`leader` = `leader`.`guid` WHERE `arenateams`.`type` =  '0' AND `arenateams`.`data` NOT LIKE  '0 0 0 0' ORDER BY `arenateams`.`rating` DESC LIMIT ";
	var $sql_select_2="SELECT `arenateams`.`name`, `arenateams`.`rating`, `arenateams`.`data`, `leader`.`name` AS `leader_name`, `arenateams`.`player_data1`AS `data_member1`, `arenateams`.`player_data2`AS `data_member2`, `arenateams`.`player_data3`AS `data_member3`, `arenateams`.`player_data4`AS `data_member4`, `arenateams`.`player_data5`AS `data_member5`, `arenateams`.`player_data6`AS `data_member6` FROM `arenateams` Inner Join `characters` AS `leader` ON `arenateams`.`leader` = `leader`.`guid` WHERE `arenateams`.`type` =  '1' AND `arenateams`.`data` NOT LIKE  '0 0 0 0' ORDER BY `arenateams`.`rating` DESC LIMIT ";
	var $sql_select_3="SELECT `arenateams`.`name`, `arenateams`.`rating`, `arenateams`.`data`, `leader`.`name` AS `leader_name`, `arenateams`.`player_data1`AS `data_member1`, `arenateams`.`player_data2`AS `data_member2`, `arenateams`.`player_data3`AS `data_member3`, `arenateams`.`player_data4`AS `data_member4`, `arenateams`.`player_data5`AS `data_member5`, `arenateams`.`player_data6`AS `data_member6`, `arenateams`.`player_data7`AS `data_member7`, `arenateams`.`player_data8`AS `data_member8`, `arenateams`.`player_data9`AS `data_member9`, `arenateams`.`player_data10`AS `data_member10` FROM `arenateams` Inner Join `characters` AS `leader` ON `arenateams`.`leader` = `leader`.`guid` WHERE `arenateams`.`type` =  '2' AND `arenateams`.`data` NOT LIKE  '0 0 0 0' ORDER BY `arenateams`.`rating` DESC LIMIT ";
	function module_toparena(){
		global $system;
		$system->cache->c_add("MySQL","arena2v2",array($this,'c_cache_2v2'));
		$system->cache->c_add("MySQL","arena3v3",array($this,'c_cache_3v3'));
		$system->cache->c_add("MySQL","arena5v5",array($this,'c_cache_5v5'));
		$this->sql_select_1.=$this->top_limit;
		$this->sql_select_2.=$this->top_limit;
		$this->sql_select_3.=$this->top_limit;
	}
	function ismenu(){
		return 1;
	}
	function isload(){
		if (!extension_loaded('mysql')) return 0;

		return 1;
	}
	function getname(){
		return "Arena";
	}
	function getlink(){
		return "arena";
	}
	function c_cache_2v2($var){
		global $system;
		extract($var,EXTR_OVERWRITE);
		$db_info=array();
		$system->cache->open("./Cache/MySQL",NULL);
		$db_info = $system->cache->read("arena2v2");
		if(!empty($db_info))
			$db_info=unserialize($db_info);
		if(is_array($db_info)){
			$frt=$db_info;
			unset($db_info);
		}else{
			$char_link=$system->mysql_connect();
			$resa1=mysql_query($this->sql_select_1,$char_link);
			$data=array();
			while ($char = mysql_fetch_array($resa1)){
				$members=array();
				for($i=1;$i<=4;$i++) {
					if(isset($char["data_member".$i]) and $char["data_member".$i]!="0 0 0 0 0"){
						list($char_guid,$m_Played_ThisWeek, $m_Won_ThisWeek,$m_Played_ThisSeason, $m_Won_ThisSeason)=explode(" ",$char["data_member".$i]);
						$res2=mysql_query("SELECT `characters`.`name`, `characters`.`race`, `characters`.`class`, `characters`.`gender` FROM `characters` WHERE `characters`.`guid` =  ".$char_guid,$char_link);
						if($res2){
							$mem=mysql_fetch_array($res2);
							@mysql_free_result($res2);
							$members[]=array('info'=>$mem,'char_guid'=>$char_guid,'Played_ThisWeek'=>$m_Played_ThisWeek,'Won_ThisWeek'=>$m_Won_ThisWeek,'Played_ThisSeason'=>$m_Played_ThisSeason,'Won_ThisSeason'=>$m_Won_ThisSeason);}
					}
				}
				$char['members']=$members;
				$data[]=$char;
			}
			@mysql_free_result($resa1);
			mysql_close($char_link);
			//var_dump($data);
			$frt=array("arena2v2"=>$data);
			$system->cache->write("arena2v2",serialize($frt));
		}
		return $frt;
	}
	function c_cache_3v3($var){
		global $system;
		extract($var,EXTR_OVERWRITE);
		$db_info=array();
		$system->cache->open("./Cache/MySQL",NULL);
		$db_info = $system->cache->read("arena3v3");
		if(!empty($db_info))
			$db_info=unserialize($db_info);
		if(is_array($db_info)){
			$frt=$db_info;
			unset($db_info);
		}else{
			$char_link=$system->mysql_connect();
			$resa2=mysql_query($this->sql_select_2,$char_link);
			$data=array();
			while ($char = mysql_fetch_array($resa2)){
				$members=array();
				for($i=1;$i<=6;$i++) {
					if(isset($char["data_member".$i]) and $char["data_member".$i]!="0 0 0 0 0"){
						list($char_guid,$m_Played_ThisWeek, $m_Won_ThisWeek,$m_Played_ThisSeason, $m_Won_ThisSeason)=explode(" ",$char["data_member".$i]);
						$res2=mysql_query("SELECT `characters`.`name`, `characters`.`race`, `characters`.`class`, `characters`.`gender` FROM `characters` WHERE `characters`.`guid` =  ".$char_guid,$char_link);
						if($res2){
							$mem=mysql_fetch_array($res2);
							@mysql_free_result($res2);
							$members[]=array('info'=>$mem,'char_guid'=>$char_guid,'Played_ThisWeek'=>$m_Played_ThisWeek,'Won_ThisWeek'=>$m_Won_ThisWeek,'Played_ThisSeason'=>$m_Played_ThisSeason,'Won_ThisSeason'=>$m_Won_ThisSeason);}
					}
				}
				$char['members']=$members;
				$data[]=$char;
			}
			@mysql_free_result($resa2);
			mysql_close($char_link);
			//var_dump($data);
			$frt=array("arena3v3"=>$data);
			$system->cache->write("arena3v3",serialize($frt));
		}
		return $frt;
	}
	function c_cache_5v5($var){
		global $system;
		extract($var,EXTR_OVERWRITE);
		$db_info=array();
		$system->cache->open("./Cache/MySQL",NULL);
		$db_info = $system->cache->read("arena5v5");
		if(!empty($db_info))
			$db_info=unserialize($db_info);
		if(is_array($db_info)){
			$frt=$db_info;
			unset($db_info);
		}else{
			$char_link=$system->mysql_connect();
			$resa3=mysql_query($this->sql_select_3,$char_link);
			$data=array();
			while ($char = mysql_fetch_array($resa3)){
				$members=array();
				for($i=1;$i<=10;$i++) {
					if(isset($char["data_member".$i]) and $char["data_member".$i]!="0 0 0 0 0"){
						list($char_guid,$m_Played_ThisWeek, $m_Won_ThisWeek,$m_Played_ThisSeason, $m_Won_ThisSeason)=explode(" ",$char["data_member".$i]);
						$res2=mysql_query("SELECT `characters`.`name`, `characters`.`race`, `characters`.`class`, `characters`.`gender` FROM `characters` WHERE `characters`.`guid` =  ".$char_guid,$char_link);
						if($res2){
							$mem=mysql_fetch_array($res2);
							@mysql_free_result($res2);
							$members[]=array('info'=>$mem,'char_guid'=>$char_guid,'Played_ThisWeek'=>$m_Played_ThisWeek,'Won_ThisWeek'=>$m_Won_ThisWeek,'Played_ThisSeason'=>$m_Played_ThisSeason,'Won_ThisSeason'=>$m_Won_ThisSeason);}
					}
				}
				$char['members']=$members;
				$data[]=$char;
			}
			@mysql_free_result($resa3);
			mysql_close($char_link);
			//var_dump($data);
			$frt=array("arena5v5"=>$data);
			$system->cache->write("arena5v5",serialize($frt));
		}
		return $frt;
	}
	function getdata(&$tpl){
		global $_CONFIG,$system,$_GET;
		require_once './inc/base.inc.php';
		$tpl->setBlock('MOD_PAGE', 'a2v2');
		$tpl->setBlock('MOD_PAGE', 'a3v3');
		$tpl->setBlock('MOD_PAGE', 'a5v5');
		extract($system->cache->c_get("MySQL","arena5v5",array('_CONFIG'=>$_CONFIG)),EXTR_OVERWRITE);
		extract($system->cache->c_get("MySQL","arena2v2",array('_CONFIG'=>$_CONFIG)),EXTR_OVERWRITE);
		extract($system->cache->c_get("MySQL","arena3v3",array('_CONFIG'=>$_CONFIG)),EXTR_OVERWRITE);
		foreach($arena2v2 as $rank => $char){
			$rank++;
			$tpl->setParam('a2v2_rank',$rank);
			$tpl->setParam('a2v2_name',"<a href=\"?m=arena&amp;inspect=a2v2&amp;id={$rank}\">{$char["name"]}</a>");
			$tpl->setParam('a2v2_leader',$char["leader_name"]);
			list($gamesplayedweek,$gameswonweek,$gamesplayedseason,$gameswonseason)=explode(" ",$char["data"]);
			$tpl->setParam('a2v2_win',$gameswonseason);
			$tpl->setParam('a2v2_lose',$gamesplayedseason-$gameswonseason);
			$tpl->setParam('a2v2_rating',$char["rating"]);
			
			$tpl->parseParam('a2v2', 'a2v2Dynamic', true);
		}
		$tpl->setParam('a2v2', $tpl->getParam('a2v2Dynamic'));
		foreach($arena3v3 as $rank => $char){
			$rank++;
			$tpl->setParam('a3v3_rank',$rank);
			$tpl->setParam('a3v3_name',"<a href=\"?m=arena&amp;inspect=a3v3&amp;id={$rank}\">{$char["name"]}</a>");
			$tpl->setParam('a3v3_leader',$char["leader_name"]);
			list($gamesplayedweek,$gameswonweek,$gamesplayedseason,$gameswonseason)=explode(" ",$char["data"]);
			$tpl->setParam('a3v3_win',$gameswonseason);
			$tpl->setParam('a3v3_lose',$gamesplayedseason-$gameswonseason);
			$tpl->setParam('a3v3_rating',$char["rating"]);
			
			$tpl->parseParam('a3v3', 'a3v3Dynamic', true);
		}
		$tpl->setParam('a3v3', $tpl->getParam('a3v3Dynamic'));
		foreach($arena5v5 as $rank => $char){
			$rank++;
			$tpl->setParam('a5v5_rank',$rank);
			$tpl->setParam('a5v5_name',"<a href=\"?m=arena&amp;inspect=a5v5&amp;id={$rank}\">{$char["name"]}</a>");
			$tpl->setParam('a5v5_leader',$char["leader_name"]);
			list($gamesplayedweek,$gameswonweek,$gamesplayedseason,$gameswonseason)=explode(" ",$char["data"]);
			$tpl->setParam('a5v5_win',$gameswonseason);
			$tpl->setParam('a5v5_lose',$gamesplayedseason-$gameswonseason);
			$tpl->setParam('a5v5_rating',$char["rating"]);
			
			$tpl->parseParam('a5v5', 'a5v5Dynamic', true);
		}
		$tpl->setParam('a5v5', $tpl->getParam('a5v5Dynamic'));

		if(isset($_GET) and isset($_GET['inspect']) and isset($_GET['id']) and $_GET['id']>0 and $_GET['id']<=$this->top_limit and ($_GET['inspect']=='a2v2' OR $_GET['inspect']=='a3v3' OR $_GET['inspect']=='a5v5')){
			if($_GET['inspect']=="a5v5")
				$s_type=$arena5v5;
			elseif($_GET['inspect']=="a3v3")
				$s_type=$arena3v3;
			elseif($_GET['inspect']=="a2v2")
				$s_type=$arena2v2;
			if(isset($s_type[$_GET['id']-1])){
				$tpl->setFile('fa_inspect',"arena_inspect.tpl");
				$tpl->parseFile('fa_inspect');
				$tpl->setBlock('fa_inspect', 'ainspect');
				$tpl->setParam('ainspect_team_name',$s_type[$_GET['id']-1]['name']);
				$tpl->setParam('MOD_NAME',"Arena - {$_GET['inspect']} - {$s_type[$_GET['id']-1]['name']}");
				require_once './inc/base.inc.php';
				$m=$s_type[$_GET['id']-1]['members'];
				//var_dump($s_type[$_GET['id']-1]);
				foreach($m as $cur){
					$tpl->setParam('ainspect_name',$cur['info']['name']);
					$tpl->setParam('ainspect_class',"<img src=\"icon/class/{$cur['info']['class']}.gif\" alt=\"{$base_class[$cur['info']['class']]}\" />");
					$tpl->setParam('ainspect_race',"<img src=\"icon/race/{$cur['info']['race']}-{$cur['info']['gender']}.gif\" alt=\"{$base_race[$cur['info']['race']]}\" />");
					$tpl->setParam('ainspect_ww',$cur['Won_ThisWeek']);
					$tpl->setParam('ainspect_wl',$cur['Played_ThisWeek']-$cur['Won_ThisWeek']);
					$tpl->setParam('ainspect_sl',$cur['Played_ThisSeason']-$cur['Won_ThisSeason']);
					$tpl->setParam('ainspect_sw',$cur['Won_ThisSeason']);
					$tpl->parseParam('ainspect', 'ainspectDynamic', true);
				}
				$tpl->setParam('ainspect', $tpl->getParam('ainspectDynamic'));
			}else{
				$tpl->setParam('fa_inspect','');
			}
		}else{
			$tpl->setParam('fa_inspect','');
		}
	}
	function gettplfile(){
		return "arena";
	}
}
?>