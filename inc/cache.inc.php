<?php
class Cache extends Storage {
	var $Cache=array();
	function c_add($name,$id,$c_update,$rc=0)
	{
		$this->Cache[$name.'_'.$id]=array('rc'=>$rc,'code'=>$c_update);
	}
	function c_get($name,$id,$var=array())
	{
		if(!isset($this->Cache[$name.'_'.$id]))
			return New Error("Cache->c_get: Storage \"{$name}_{$id}\" is not defined",0);
		if(!is_callable($this->Cache[$name.'_'.$id]['code']))
			return New Error("Cache->c_get: Storage \"{$name}_{$id}\" not callable",0);
		return call_user_func($this->Cache[$name.'_'.$id]['code'],$var);
	}
}
$Cache=new Cache();
//--------------------------------------
function c_cache_xml($var){
	global $Cache;
	extract($var,EXTR_OVERWRITE);
	$XML_DATA=array();
	foreach($_CONFIG['stats.xml'] as $k => $v){
	if(@$_GET['m'] == "onl" OR $k==0){
		$xml_info=array();
		$Cache->open("./Cache/XML",NULL);
		$xml_info = $Cache->read("xml-stats_".$k);
		if(!empty($xml_info)){
			$xml_info=unserialize($xml_info);
			if($xml_info["mtime"]<=1 OR $xml_time[$k]<>$xml_info["mtime"]){
				$Cache->destroy("xml-stats_".$k);
				$xml_info="";
			}
		}
		if(is_array($xml_info))
		{
			//extract($xml_info,EXTR_OVERWRITE);
			$XML_DATA[$k]=$xml_info['data'];
			unset($xml_info);
			unset($mtime);
		}else{
			require_once "./inc/XMLParser.php";
			$p=new PEAR_XMLParser;
			if($p->parse(implode("",file($v)),$v,$k)){
				$data=$p->getData();
				$Cache->write("xml-stats_".$k,serialize(array("data"=>$data,"mtime"=>$xml_time)));
				$XML_DATA[$k]=$data;
			}
		}
	}}
	return array("xml_data"=>$XML_DATA);
}
$Cache->c_add("XML","xml-stats",'c_cache_xml');
//-----------------------------------------------------
function c_cache_MySQL_account($var){
global $Cache;
extract($var,EXTR_OVERWRITE);
$db_info=array();
$Cache->open("./Cache/MySQL",NULL);
$db_info = $Cache->read("account");
if(!empty($db_info))
	$db_info=unserialize($db_info);
if(is_array($db_info))
{
	$frt=$db_info;
}else{
	if(!isset($login_link))
	{
		$login_link_st=true;
		$login_link=@mysql_connect($_CONFIG["MySQL_login_host"],$_CONFIG["MySQL_login_user"],$_CONFIG["MySQL_login_password"],true) or trigger_error("MySQL Err<br> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
		if(!@mysql_select_db($_CONFIG["MySQL_login_db"],$login_link))
		 trigger_error("MySQL Err<br> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
	}else{
		$login_link_st=false;
	}
	$result=mysql_query("SELECT count(*) FROM `accounts`",$login_link);
	$count_acc=mysql_result($result,0);
	@mysql_free_result($result);
	$result=mysql_query("SELECT count(*) FROM `accounts` WHERE `gm` NOT LIKE \'\'",$login_link);
	$count_gm=@mysql_result($result,0);
	$frt=array("count_acc"=>$count_acc,"count_gm"=>$count_gm);
	$Cache->write("account",serialize($frt));
	@mysql_free_result($result);
	if($login_link_st)
		mysql_close($login_link);
}
return $frt;
unset($db_info);
}
$Cache->c_add("MySQL","account",'c_cache_MySQL_account');
?>
