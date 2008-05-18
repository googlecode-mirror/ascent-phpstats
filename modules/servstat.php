<?php 
class module_servstat extends module_obj
{
	function ismenu(){
		return 1;
	}
	function isload(){
		if (!extension_loaded('mysql')) return 0;

		return 1;
	}
	function getname(){
		return "Serv Info";
	}
	function getlink(){
		return "stat";
	}
	function c_cache($var){
global $system;
extract($var,EXTR_OVERWRITE);
$db_info=array();
$system->cache->open("./Cache/MySQL",NULL);
$db_info = $system->cache->read("characters");
if(!empty($db_info))
	$db_info=unserialize($db_info);
if(is_array($db_info))
{
	$frt=$db_info;
	unset($db_info);
}else{
	$char_link=$system->mysql_connect();
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
	$system->cache->write("characters",serialize($frt));
	mysql_close($char_link);
}
return $frt;
	}
	function getdata(&$tpl){
		global $system,$_CONFIG;
extract($system->cache->c_get("MySQL","characters",array('_CONFIG'=>$_CONFIG)),EXTR_OVERWRITE);
if($c<1)
{
	for($i=1;$i<=24;$i++)
	{
		$tpl->setParam('S_'.$i,"0% (0)");
	}
}else{
	$tpl->setParam('S_2',round(($ally['Alliance'] * 100 ) / $c) ."%(".number_format($ally['Alliance']).")");
	$tpl->setParam('S_3',(100 - round(($ally['Alliance'] * 100 ) / $c))."%(".number_format($c - $ally['Alliance']).")");
	$tpl->setParam('S_4',number_format($c));

	$tpl->setParam('S_5',round(($races['Human'] * 100 ) / $c) ."%(".number_format($races['Human']).")");
	$tpl->setParam('S_7',round(($races['Dwarf'] * 100 ) / $c) ."%(".number_format($races['Dwarf']).")");
	$tpl->setParam('S_8',round(($races['Tauren'] * 100 ) / $c) ."%(".number_format($races['Tauren']).")");
	$tpl->setParam('S_10',round(($races['Draenei'] * 100 ) / $c) ."% (".number_format($races['Draenei']).")");
	$tpl->setParam('S_11',round(($races['Blood Elf'] * 100 ) / $c) ."%(".number_format($races['Blood Elf']).")");
	$tpl->setParam('S_13',round(($races['Night Elf'] * 100 ) / $c) ."%(".number_format($races['Night Elf']).")");
	$tpl->setParam('S_14',round(($races['Orc'] * 100 ) / $c) ."%(".number_format($races['Orc']).")");
	$tpl->setParam('S_16',round(($races['Gnom'] * 100 ) / $c) ."%(".number_format($races['Gnom']).")");
	$tpl->setParam('S_17',round(($races['Troll'] * 100 ) / $c) ."%(".number_format($races['Troll']).")");
	$tpl->setParam('S_24',round(($races['Undead'] * 100 ) / $c) ."%(".number_format($races['Undead']).")");
	
	$tpl->setParam('S_9',round(($classes['Paladin'] * 100 ) / $c) ."%(".number_format($classes['Paladin']).")");
	$tpl->setParam('S_12',round(($classes['Hunter'] * 100 ) / $c) ."%(".number_format($classes['Hunter']).")");
	$tpl->setParam('S_15',round(($classes['Warrior'] * 100 ) / $c) ."%(".number_format($classes['Warrior']).")");
	$tpl->setParam('S_18',round(($classes['Rogue'] * 100 ) / $c) ."%(".number_format($classes['Rogue']).")");
	$tpl->setParam('S_19',round(($classes['Priest'] * 100 ) / $c) ."%(".number_format($classes['Priest']).")");
	$tpl->setParam('S_20',round(($classes['Shaman'] * 100 ) / $c) ."%(".number_format($classes['Shaman']).")");
	$tpl->setParam('S_21',round(($classes['Mage'] * 100 ) / $c) ."%(".number_format($classes['Mage']).")");
	$tpl->setParam('S_22',round(($classes['Warlock'] * 100 ) / $c) ."%(".number_format($classes['Warlock']).")");
	$tpl->setParam('S_23',round(($classes['Druid'] * 100 ) / $c) ."%(".number_format($classes['Druid']).")");
}	
//-----
extract($system->cache->c_get("MySQL","account",array('_CONFIG'=>$_CONFIG)),EXTR_OVERWRITE);
$tpl->setParam('S_1',number_format($count_acc));
$tpl->setParam('S_6',number_format($count_gm));
	}
	function gettplfile(){
		return "servstat";
	}
	function module_servstat(){
		global $system;
		$system->cache->c_add("MySQL","characters",array($this,'c_cache'));
	}
}
?>