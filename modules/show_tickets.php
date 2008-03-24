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
		return "show_tickets";
	}
	function getdata(&$tpl){
		global $Cache,$_CONFIG,$_GET;
		$lnk="";
		if(isset($_GET) AND isset($_GET['act']) AND is_admin(getenv('REMOTE_ADDR'))){
			if($_GET['act']=="del" and isset($_GET['id'])){
				$char_link=@mysql_connect($_CONFIG["MySQL_char_host"],$_CONFIG["MySQL_char_user"],$_CONFIG["MySQL_char_password"],true) or trigger_error("MySQL Err<br> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
				if(!@mysql_select_db($_CONFIG["MySQL_char_db"],$char_link))trigger_error("MySQL Err<br> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
				mysql_query("DELETE  FROM  `gm_tickets` WHERE `guid`=".$_GET['id'],$char_link);
				$Cache->open("./Cache/MySQL",NULL);
				$Cache->destroy('tickets');
				$lnk.="
<script type=\"text/javascript\">
self.location=\"?m=".$this->getlink()."\";
</script>";
			}
		}
		$tpl->setBlock('MOD_PAGE', 'ST');
		if(isset($char_link))
		extract($Cache->c_get("MySQL","tickets",array('_CONFIG'=>$_CONFIG,'char_link'=>$char_link)),EXTR_OVERWRITE);
		else
		extract($Cache->c_get("MySQL","tickets",array('_CONFIG'=>$_CONFIG)),EXTR_OVERWRITE);

		$params = array(
    'itemData' => $data,
    'perPage' => 10,
    'delta' => 8,             // for 'Jumping'-style a lower number is better
    'append' => true,
    'separator' => ' | ',
    'clearIfVoid' => false,
    'urlVar' => 'pd',
//    'useSessions' => true,
//    'closeSession' => true,
//    'mode'  => 'Sliding',    //try switching modes
    'mode'  => 'Jumping',

);
		$pager = & Pager::factory($params);
		$page_data = $pager->getPageData();
		$links = $pager->getLinks();
		//print_r($links);
		foreach ($page_data as $key => $val) {
			if(is_admin(getenv('REMOTE_ADDR')))
			$tpl->setParam('ST_ID',"Name:{$val['name']}({$val['level']})"."\n<br/> id:{$val['guid']}<pre> <a href=\"?m=".$this->getlink()."&amp;act=del&amp;id={$val['guid']}\">DELETE</a>");
			else
			$tpl->setParam('ST_ID',"Name:{$val['name']}({$val['level']})"."\n<br/> id:{$val['guid']}");
			$tpl->setParam('ST_MESS',$val['message']);
			$tpl->setParam('ST_TIME', date("H:i:s d.m.y",$val['timestamp']));
			$tpl->parseParam('ST', 'STDynamic', true);
		}
		if(isset($links['back']) AND $links['back']!="")
		$lnk.="<div align=\"left\">".$links['back']."</div>";
		$lnk.="<div align=\"center\">".$links['pages']."</div>";
		if(isset($links['next']) AND $links['next']!="")
		$lnk.="<div align=\"right\">".$links['next']."</div>";
		if(is_admin(getenv('REMOTE_ADDR')))
		$tpl->setParam('ST_PAGE',$lnk."\n<pre><a href=\"?m=".$this->getlink()."&amp;act=update\">UPDATE</a>");
		else
		$tpl->setParam('ST_PAGE',$lnk);

		$tpl->setParam('ST', $tpl->getParam('STDynamic'));
	}
	function gettplfile(){
		return "st";
	}
	function c_cache($var){
global $Cache;
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
$Cache->open("./Cache/MySQL",NULL);
$db_info = $Cache->read("tickets");
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
		$char_link=@mysql_connect($_CONFIG["MySQL_char_host"],$_CONFIG["MySQL_char_user"],$_CONFIG["MySQL_char_password"],true) or trigger_error("MySQL Err<br> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
		if(!@mysql_select_db($_CONFIG["MySQL_char_db"],$char_link))
		 trigger_error("MySQL Err<br> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
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
	$Cache->write("tickets",serialize($frt));
	//if($char_link_st)
		mysql_close($char_link);
}
return $frt;
unset($db_info);
	}
	function module_show_tickets(){
		global $Cache;
		$Cache->c_add("MySQL","tickets",array($this,'c_cache'));
	}
}
?>