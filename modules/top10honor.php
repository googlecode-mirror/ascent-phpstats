<?php 
class module_top10honor extends module_obj
{
	var $sql_select="SELECT `characters`.`name`, `characters`.`race`, `characters`.`class`, `characters`.`gender`, `characters`.`level`, `characters`.`killsLifeTime` FROM `characters` ORDER BY `characters`.`killsLifeTime` DESC LIMIT 100";
	function module_top10honor(){
		global $system;
		$system->cache->c_add("MySQL","tk",array($this,'c_cache'));
	}
	function ismenu(){
		return 1;
	}
	function isload(){
		//if(!is_admin(getenv('REMOTE_ADDR'))) return 0;
		if (!extension_loaded('mysql')) return 0;

		return 1;
	}
	function getname(){
		return "ToP Kills";
	}
	function getlink(){
		return "t10k";
	}
	function c_cache($var){
		global $system;
		extract($var,EXTR_OVERWRITE);
		$db_info=array();
		$system->cache->open("./Cache/MySQL",NULL);
		$db_info = $system->cache->read("tk");
		if(!empty($db_info))
			$db_info=unserialize($db_info);
		if(is_array($db_info)){
			$frt=$db_info;
			unset($db_info);
		}else{
			$char_link=$system->mysql_connect();
			$res=mysql_query($this->sql_select,$char_link);
			$data=array();
			while ($char = mysql_fetch_array($res)){
				$data[]=$char;
			}
			@mysql_free_result($res);
			mysql_close($char_link);
			$frt=array("top10data"=>$data);
			$system->cache->write("tk",serialize($frt));
		}
		return $frt;
	}
	function getdata(&$tpl){
		global $_CONFIG,$system,$_GET;
		require_once './inc/base.inc.php';
		$tpl->setBlock('MOD_PAGE', 'TOP10');
		extract($system->cache->c_get("MySQL","tk",array('_CONFIG'=>$_CONFIG)),EXTR_OVERWRITE);
		if(isset($_GET['t'])) $t=$_GET['t']; else $t=1;
		if($t<1 OR $t>3) $t=1;
		foreach($top10data as $rank => $char){
			if($t==1){
				if($rank>=10) break;
			}elseif($t==2){
				if($rank>=50) break;
			}elseif($t==3){
				if($rank>=100) break;
			}
			$tpl->setParam('top_rank',$rank+1);
			$tpl->setParam('top_name',$char["name"]);
			$tpl->setParam('top_race',"<img src=\"icon/race/{$char['race']}-{$char['gender']}.gif\" alt=\"{$base_race[$char['race']]}\" />");
			$tpl->setParam('top_class',"<img src=\"icon/class/{$char['class']}.gif\" alt=\"{$base_class[$char['class']]}\" />");
			$tpl->setParam('top_kill',$char["killsLifeTime"]);
			$tpl->setParam('top_level',$char["level"]);
			
			$tpl->parseParam('TOP10', 'TOP10Dynamic', true);
		}
		$tpl->setParam('TOP10', $tpl->getParam('TOP10Dynamic'));
	}
	function gettplfile(){
		return "top10kill";
	}
}
?>