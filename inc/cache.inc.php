<?php
class Cache extends Storage {
	var $Cache=array();
	var $system;
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
$Cache = $system->hcache(new Cache());
//--------------------------------------
function c_cache_xml($var,$f_try=0){
	global $system;
	extract($var,EXTR_OVERWRITE);
	$XML_DATA=array();
	$xml_info=array();
	$system->cache->open("./Cache/XML",NULL);
	$xml_info = $system->cache->read("xml-stats");
	if(!is_array($system->sl) and !isset($system->sl[$system->as])) $v=$system->sl[0]['xml'];
	else
	$v=$system->sl[$system->as]['xml'];
	if(!empty($xml_info)){
		$xml_info=unserialize($xml_info);
		if($xml_info["mtime"]<=1 OR $xml_time<>$xml_info["mtime"]){
			if($xml_time<>-2){
			$system->cache->destroy("xml-stats");
			$xml_info="";}
		}
		if(is_array($xml_info)){
			//extract($xml_info,EXTR_OVERWRITE);
			$XML_DATA=$xml_info['data'];
			unset($xml_info);
			unset($mtime);
		}
	}else{
		require_once "./inc/XMLParser.php";
		$p=new PEAR_XMLParser;
		if($p->parse(implode("",file($v)),$v,0)){
			$data=$p->getData();
			$system->cache->write("xml-stats",serialize(array("data"=>$data,"mtime"=>$xml_time)));
			$XML_DATA=$data;
		}
	}
	$system->xml=$XML_DATA;
	if((!is_array($system->xml) OR !isset($system->xml['instances']) OR !isset($system->xml['instances']['instance']) OR !isset($system->xml['status']) OR !is_array($system->xml['status']) OR !is_array($system->xml['instances']['instance'])) AND $f_try<5){
		sleep(0.1);
		$a=c_cache_xml($var,$f_try+1);
	}
	return array("xml_data"=>$XML_DATA);
}
$system->cache->c_add("XML","xml-stats",'c_cache_xml');
//-----------------------------------------------------
function c_cache_MySQL_account($var){
global $system;
extract($var,EXTR_OVERWRITE);
$db_info=array();
$system->cache->open("./Cache/MySQL",NULL);
$db_info = $system->cache->read("account");
if(!empty($db_info))
	$db_info=unserialize($db_info);
if(is_array($db_info))
{
	$frt=$db_info;
}else{
	if(!isset($login_link))
	{
		$login_link_st=true;
		$login_link=$system->mysql_login();
	}else{
		$login_link_st=false;
	}
	$result=mysql_query("SELECT count(*) FROM `accounts`",$login_link);
	$count_acc=mysql_result($result,0);
	@mysql_free_result($result);
	$result=mysql_query("SELECT count(*) FROM `accounts` WHERE `gm` NOT LIKE ''",$login_link);
	$count_gm=@mysql_result($result,0);
	$frt=array("count_acc"=>$count_acc,"count_gm"=>$count_gm);
	$system->cache->write("account",serialize($frt));
	@mysql_free_result($result);
	if($login_link_st)
		mysql_close($login_link);
}
return $frt;
unset($db_info);
}
$system->cache->c_add("MySQL","account",'c_cache_MySQL_account');
?>
