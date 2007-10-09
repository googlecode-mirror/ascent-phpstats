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
		extract($var,EXTR_OVERWRITE);
		$frt=array();
		eval($this->Cache[$name.'_'.$id]['code']);
		return $frt;
	}
}
$Cache=new Cache();
//--------------------------------------
$Cache->c_add("XML","xml-stats",'
$xml_info=array();
$this->open("./Cache/XML",NULL);
$xml_info = $this->read("xml-stats");
if(!empty($xml_info)){
	$xml_info=unserialize($xml_info);
	if($xml_info["mtime"]<=1 OR $xml_time<>$xml_info["mtime"]){
		$this->destroy("xml-stats");
		$xml_info="";
	}
}
if(is_array($xml_info))
{
	//extract($xml_info,EXTR_OVERWRITE);
	$frt=$xml_info;
	unset($xml_info);
	unset($mtime);
}else{
	require_once "./inc/XMLParser.php";
	$p=new PEAR_XMLParser;
	$p->parse(implode("",file($_CONFIG["stats.xml"])));
	$data=$p->getData();
	$this->write("xml-stats",serialize(array("data"=>$data,"mtime"=>$xml_time)));
	$frt=array("data"=>$data);
}');
//-----------------------------------------------------
$Cache->c_add("MySQL","characters",'
$db_info=array();
$this->open("./Cache/MySQL",NULL);
$db_info = $this->read("characters");
if(!empty($db_info))
	$db_info=unserialize($db_info);
if(is_array($db_info))
{
	$frt=$db_info;
	unset($db_info);
}else{
	$char_link=@mysql_connect($_CONFIG["MySQL_char_host"],$_CONFIG["MySQL_char_user"],$_CONFIG["MySQL_char_password"],true) or trigger_error("MySQL Err<br> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
	if(!@mysql_select_db($_CONFIG["MySQL_char_db"],$char_link))
	 trigger_error("MySQL Err<br> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
	$result1=mysql_query("SELECT `race`,`class` FROM `characters` WHERE `level`>".$_CONFIG["lvlmin"],$char_link);
	$c=mysql_num_rows($result1);
	$races=array("Human"=>0,"Orc"=>0,"Dwarf"=>0,"Night Elf"=>0,"Undead"=>0,"Tauren"=>0,"Gnom"=>0,"Troll"=>0,"Blood Elf"=>0,"Draenei"=>0);
	$ally=array("Alliance"=>0);
	$classes=array("Warrior"=>0,"Paladin"=>0,"Hunter"=>0,"Rogue"=>0,"Priest"=>0,"Shaman"=>0,"Mage"=>0,"Warlock"=>0,"Druid"=>0);
	while ($char = mysql_fetch_array($result1))
	{
	switch 	($char["race"])
		{
		case 1: 
			$races["Human"]++;
			$ally["Alliance"]++;
			break;
		case 2: 
			$races["Orc"]++;
			break;
		case 3: 
			$races["Dwarf"]++;
			$ally["Alliance"]++;
			break;
		case 4: 
			$races["Night Elf"]++;
			$ally["Alliance"]++;
			break;
		case 5: 
			$races["Undead"]++;
			break;
		case 6:  
			$races["Tauren"]++;
			break;
		case 7:  
			$races["Gnom"]++;
			$ally["Alliance"]++;
			break;
		case 8:  
			$races["Troll"]++;
			break;
		case 10:	
			$races["Blood Elf"]++;
			break;
		case 11: 
			$races["Draenei"]++;
			$ally["Alliance"]++;
			break;
			}
	switch 	($char["class"])
		{
		case 1: $classes["Warrior"]++; break;
		case 2: $classes["Paladin"]++; break;
		case 3: $classes["Hunter"]++; break;
		case 4: $classes["Rogue"]++; break;
		case 5: $classes["Priest"]++; break;
		case 7: $classes["Shaman"]++; break;
		case 8: $classes["Mage"]++; break;
		case 9: $classes["Warlock"]++; break;
		case 11: $classes["Druid"]++; break;
		}
	}
	@mysql_free_result($result1);

	@mysql_free_result($result);
	$frt=array("classes"=>$classes,"ally"=>$ally,"races"=>$races,"c"=>$c);
	$this->write("characters",serialize($frt));
	mysql_close($char_link);
}');
$Cache->c_add("MySQL","account",'
$db_info=array();
$this->open("./Cache/MySQL",NULL);
$db_info = $this->read("account");
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
	$count_gm=mysql_result($result,0);
	$frt=array("count_acc"=>$count_acc,"count_gm"=>$count_gm);
	$this->write("account",serialize($frt));
	@mysql_free_result($result);
	if($login_link_st)
		mysql_close($login_link);
}
unset($db_info);');
?>
