<?php 
class module_show_tickets extends module_obj
{
	function ismenu(){
		return 0;
	}
	function isload(){
		if (!extension_loaded('mysql')) return 0;
		return 1;
	}
	function getname(){
		return "tickets";
	}
	function getlink(){
		return "st";
	}
	function getdata(&$tpl){
		global $system,$_CONFIG,$_GET,$is_admin;
		$lnk="";
		if(isset($_GET) AND isset($_GET['act']) AND $is_admin){
			if($_GET['act']=="del" and isset($_GET['id'])){
				$char_link=$system->mysql_connect();
				mysql_query("DELETE  FROM  `gm_tickets` WHERE `guid`=".$_GET['id'],$char_link);
				$system->cache->open("./Cache/MySQL",NULL);
				$system->cache->destroy('tickets');
				$lnk.="<script type=\"text/javascript\">self.location=\"?m=".$this->getlink()."\";</script>";
			}elseif($_GET['act']=="update"){
				$system->cache->open("./Cache/MySQL",NULL);
				$system->cache->destroy('tickets');
				$lnk.="<script type=\"text/javascript\">self.location=\"?m=".$this->getlink()."\";</script>";
			}elseif($_GET['act']=="unstuck" AND isset($_GET['cname']) AND isset($system->mods->mods['uns'])){
				$char_link=$system->mysql_connect();
				$sq=mysql_query("SELECT `characters`.`guid` FROM `characters` WHERE `characters`.`name` =  '".mysql_escape_string($_GET['cname'])."' ORDER BY `characters`.`level` DESC",$char_link);
				if(mysql_num_rows($sq)==1 AND !$system->is_online($_GET['cname'])){
					$dr=mysql_fetch_array($sq);
					$system->mods->mods['uns']->makeunstuck($char_link,$dr['guid']);
				}
				$lnk.="<script type=\"text/javascript\">self.location=\"?m=".$this->getlink()."\";</script>";
			}
		}
		$tpl->setBlock('MOD_PAGE', 'ST');
		if(isset($char_link))
		extract($system->cache->c_get("MySQL","tickets",array('_CONFIG'=>$_CONFIG,'char_link'=>$char_link)),EXTR_OVERWRITE);
		else
		extract($system->cache->c_get("MySQL","tickets",array('_CONFIG'=>$_CONFIG)),EXTR_OVERWRITE);

		$params = array('itemData' => $data,'perPage' => 10,'delta' => 8,'append' => true,'separator' => ' | ','clearIfVoid' => false,'urlVar' => 'pd','mode'  => 'Jumping');
		$pager = & Pager::factory($params);
		$page_data = $pager->getPageData();
		$links = $pager->getLinks();
		//print_r($links);
		foreach ($page_data as $key => $val) {
			$isonline=$system->is_online($val['name']);
			$n="Name:{$val['name']}({$val['level']})"."\n<br/> id:{$val['guid']}<br/>";
			if($isonline) $n.="Online"; else $n.="Offline";
			if($is_admin){// $n.="<hr/>";
				if(isset($system->mods->mods['uns']) AND !$isonline) $n.="<hr/><a href=\"?m=".$this->getlink()."&amp;act=unstuck&amp;cname={$val['name']}\">UNSTUCK</a>";
				if(isset($system->mods->mods['lookup'])) $n.="<hr/><a href=\"?m=lookup&amp;char={$val['name']}\">LOOKUP</a>";

				$n.="<hr/>&nbsp;<br/><a href=\"?m=".$this->getlink()."&amp;act=del&amp;id={$val['guid']}\">DELETE</a>";
			}
			
			$tpl->setParam('ST_ID',$n);
			$parser = new HTML_BBCodeParser();
			$parser->setText(nl2br(htmlentities($val['message'],HTML_ENTITIES,'UTF-8')));
			$parser->parse();
			$tpl->setParam('ST_MESS',$parser->getParsed());
			$tpl->setParam('ST_TIME', date("H:i:s d.m.y",$val['timestamp']));
			$tpl->parseParam('ST', 'STDynamic', true);
		}
		if(isset($links['back']) AND $links['back']!="")
		$lnk.="<div align=\"left\">".$links['back']."</div>";
		$lnk.="<div align=\"center\">".$links['pages']."</div>";
		if(isset($links['next']) AND $links['next']!="")
		$lnk.="<div align=\"right\">".$links['next']."</div>";
		if($is_admin)
		$tpl->setParam('ST_PAGE',$lnk."\n<pre><a href=\"?m=".$this->getlink()."&amp;act=update\">UPDATE</a>");
		else
		$tpl->setParam('ST_PAGE',$lnk);

		$tpl->setParam('ST', $tpl->getParam('STDynamic'));
	}
	function gettplfile(){
		return "st";
	}
	function c_cache($var){
global $system;
extract($var,EXTR_OVERWRITE);
$SQL_SELECT="
SELECT
`gm_tickets`.`guid`,
`gm_tickets`.`name`,
`gm_tickets`.`level`,
`gm_tickets`.`timestamp`,
`gm_tickets`.`message`,
`gm_tickets`.`type`
FROM
`gm_tickets`
ORDER BY
`gm_tickets`.`timestamp` DESC";
$db_info=array();
$system->cache->open("./Cache/MySQL",NULL);
$db_info = $system->cache->read("tickets");
if(!empty($db_info))
	$db_info=unserialize($db_info);
if(is_array($db_info))
{
	$frt=$db_info;
}else{
	$data=array();
	if(!isset($char_link))
	{
		$char_link_st=true;
		$char_link=$system->mysql_connect();
	}else{
		$char_link_st=false;
	}
	mysql_query("SET NAMES UTF8",$char_link);
	$result=mysql_query($SQL_SELECT,$char_link);
	while ($dr = mysql_fetch_array($result))
	{
		$data[]=$dr;
	}
	@mysql_free_result($result);
	$frt=array("data"=>$data);
	$system->cache->write("tickets",serialize($frt));
	//if($char_link_st)
		mysql_close($char_link);
}
return $frt;
unset($db_info);
	}
	function module_show_tickets(){
		global $system;
		$system->cache->c_add("MySQL","tickets",array($this,'c_cache'));
	}
}
?>