<?php
if (!extension_loaded('mysql')) {
	die("MYSQL Extension not found");
}
ini_set('track_errors', TRUE);
require_once './inc/basic.inc.php';
$MTimer = new MTimer;
$MTimer->start();
require_once './inc/base.inc.php';
require_once './inc/object.inc.php';
require_once './inc/template.inc.php';
require_once './inc/sgfiles.inc.php';
require_once './inc/file.inc.php';
require_once './inc/dir.inc.php';
$_CONFIG=array();
$Cache=new Storage();
require_once './config.php';
require_once './inc/gzip.inc.php';
require_once './inc/reg.inc.php';

$Cache->open("./Cache/MySQL",NULL);
$Cache->gc($_CONFIG['statistics_update_time']);

$xml_info=array();
$Cache->open("./Cache/XML",NULL);
if(substr($_CONFIG['stats.xml'],0,5) == "http:")
{
	if(!ini_get("allow_url_fopen"))
		die("Error, HTTP_File: \"".$_CONFIG['stats.xml']."\"\n<br>\n<center>to enable this option you must set allow_url_fopen=1 in php.ini</center>");
	$h1=get_headers($_CONFIG['stats.xml'], 1);
	print_r($h1);
	if(!is_array($h1))
		die("Error, HTTP_File: \"".$_CONFIG['stats.xml']."\" not exists");
	if(!isset($h1[0]))
		$h2=explode(" ",$h1['status']);
	else
		$h2=explode(" ",$h1[0]);
	if($h2[1]!=200)
		die("Error, HTTP_File: \"".$_CONFIG['stats.xml']."\"\n<br>".$h1[0]);
	//print_r($h1);
	$xml_time = strtotime($h1['Last-Modified']);
	unset($h1);
	unset($h2);
}else{
	$xml_stats=new File($_CONFIG['stats.xml'], 'r');
	if(!$xml_stats->exists())
		die("Error, File: \"".$_CONFIG['stats.xml']."\" not exists");
	//if (!$xml_stats->open()) 
	//	die("Error, File: \"".$_CONFIG['stats.xml']."\" can't read");
	$xml_time=$xml_stats->getMTime();
	object_checkError($xml_time);
	$xml_stats->close();
}

$xml_info = $Cache->read('xml-stats');
if(!empty($xml_info)){
	$xml_info=unserialize($xml_info);
	//die($xml_time."<>".$xml_info['mtime']);
	if($xml_info['mtime']<=1 OR $xml_time<>$xml_info['mtime']){
		$Cache->destroy('xml-stats');
		$xml_info="";
	}
}
if(is_array($xml_info))
{
	extract($xml_info,EXTR_OVERWRITE);
	unset($xml_info);
	unset($mtime);
}else{
	require_once './inc/XMLParser.php';
	$p=new PEAR_XMLParser;
	$p->parse(implode('',file($_CONFIG['stats.xml'])));
	$data=$p->getData();
	$Cache->write('xml-stats',serialize(array('data'=>$data,'mtime'=>$xml_time)));
}

$tpl= new Template($_CONFIG['tpl']);
$tpl->setFile('page',$_CONFIG['tpl_filename']);
$tpl->parseFile('page');
$tpl->setBlock('page', 'Inst');
$tpl->setBlock('page', 'GM');
$tpl->setBlock('page', 'PL');

$list_inst=$data['instances']['instance'];
usort($list_inst,"sortbyplayers");
foreach ($list_inst as $key => $val) {
	if(isset($base_map[$val['map']]))
	{
		$tpl->setParam('map_name',$base_map[$val['map']]);
	}else{
		$tpl->setParam('map_name',"Unknown");
	}
	$tpl->setParam('map',$val['map']);
	if($val['maxplayers']<1)
	{
		$tpl->setParam('Inst_maxplayers',"-");
	}else{
		$tpl->setParam('Inst_maxplayers',$val['maxplayers']);
	}
	$tpl->setParam('Inst_state',$val['state']);
	$tpl->setParam('Inst_players',$val['players']);
	$tpl->setParam('Inst_creationtime',$val['creationtime']);
	$tpl->setParam('Inst_expirytime',$val['expirytime']);
	$tpl->setParam('Inst_World',$base_maptype[$val['maptype']]);
	$tpl->parseParam('Inst', 'InstDynamic', true);
}
$tpl->setParam('Inst', $tpl->getParam('InstDynamic'));
if(is_array($data['gms']))
{
	$list_gm=@$data['gms']['gmplr'];
}else{
	$list_gm=array();
}
if(!is_array($list_gm) OR count($list_gm)<1)
{
	$list_gm=array();
}else{
	if(!isset($list_gm[0])){
		$list_gm=array(0=>$list_gm);
	}
	usort($list_gm,"sortbylevel");
}

foreach ($list_gm as $key => $val) {
	$tpl->setParam('GM_name',$val['name']);
	$tpl->setParam('GM_race',$val['race']);
	$tpl->setParam('GM_class',$val['class']);
	$tpl->setParam('GM_gender',$val['gender']);
	$tpl->setParam('GM_level',$val['level']);
	$tpl->setParam('GM_permissions',$val['permissions']);
	$tpl->setParam('GM_ontime',$val['ontime']);
	$tpl->setParam('GM_ms',$val['latency']);
	$tpl->setParam('GM_class_name',$base_class[$val['class']]);
	$tpl->setParam('GM_race_name',$base_race[$val['race']]);
	$tpl->parseParam('GM', 'GMDynamic', true);
}
$tpl->setParam('GM', $tpl->getParam('GMDynamic'));
if(is_array($data['sessions']))
{
	$list_pl=@$data['sessions']['plr'];
}else{
	$list_pl=array();
}
if(!is_array($list_pl) OR count($list_pl)<1)
{
	$list_pl=array();
}else{
	if(!isset($list_pl[0])){
		$list_pl=array(0=>$list_pl);
	}
	usort($list_pl,"sortbylevel");
}
foreach ($list_pl as $key => $val) {
	$tpl->setParam('PL_name',$val['name']);
	$tpl->setParam('PL_class',$val['class']);
	$tpl->setParam('PL_gender',$val['gender']);
	$tpl->setParam('PL_class_name',$base_class[$val['class']]);
	$tpl->setParam('PL_race',$val['race']);
	$tpl->setParam('PL_race_name',$base_race[$val['race']]);
	$r = $val['race'];
	if($r==1 OR $r==3 OR $r==4 OR $r==7 OR $r==11)
	{ 
		$tpl->setParam('PL_faction',0);
		$tpl->setParam('PL_faction_name',$base_faction[0]);
	}else{
		$tpl->setParam('PL_faction',1);
		$tpl->setParam('PL_faction_name',$base_faction[1]);
	}
	$tpl->setParam('PL_level',$val['level']);
	if(isset($base_map[$val['map']]))
	{
		$tpl->setParam('PL_map',$base_map[$val['map']]);
	}else{
		$tpl->setParam('PL_map',"Unknown");
	}
	if(isset($base_areaid[$val['areaid']]))
	{
		$tpl->setParam('PL_areaid',$base_areaid[$val['areaid']]);
	}else{
		$tpl->setParam('PL_areaid',"Unknown");
	}
	$tpl->setParam('PL_ontime',$val['ontime']);
	$tpl->setParam('PL_latency',$val['latency']);
	$tpl->parseParam('PL', 'PLDynamic', true);
}
$tpl->setParam('PL', $tpl->getParam('PLDynamic'));
$info=$data['status'];
foreach($info as $key=>$val)
{
	if(isset($val))
	{
		$tpl->setParam(strtolower($key),$val);
	}
}

$db_info=array();
$Cache->open("./Cache/MySQL",NULL);
$db_info = $Cache->read('characters');
if(!empty($db_info))
	$db_info=unserialize($db_info);
if(is_array($db_info))
{
	extract($db_info,EXTR_OVERWRITE);
	unset($db_info);
}else{
	$char_link=@mysql_connect($_CONFIG['MySQL_char_host'],$_CONFIG['MySQL_char_user'],$_CONFIG['MySQL_char_password'],true) or trigger_error("MySQL Err<br> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
	if(!@mysql_select_db($_CONFIG['MySQL_char_db'],$char_link))
	 trigger_error("MySQL Err<br> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
	$result1=mysql_query("SELECT `race`,`class` FROM `characters` WHERE `level`>".$_CONFIG['lvlmin'],$char_link);
	$c=mysql_num_rows($result1);
	$races=array('Human'=>0,'Orc'=>0,'Dwarf'=>0,'Night Elf'=>0,'Undead'=>0,'Tauren'=>0,'Gnom'=>0,'Troll'=>0,'Blood Elf'=>0,'Draenei'=>0);
	$ally=array('Alliance'=>0);
	$classes=array('Warrior'=>0,'Paladin'=>0,'Hunter'=>0,'Rogue'=>0,'Priest'=>0,'Shaman'=>0,'Mage'=>0,'Warlock'=>0,'Druid'=>0);
	while ($char = mysql_fetch_array($result1))
	{
	switch 	($char['race'])
		{
		case 1: 
			$races['Human']++;
			$ally['Alliance']++;
			break;
		case 2: 
			$races['Orc']++;
			break;
		case 3: 
			$races['Dwarf']++;
			$ally['Alliance']++;
			break;
		case 4: 
			$races['Night Elf']++;
			$ally['Alliance']++;
			break;
		case 5: 
			$races['Undead']++;
			break;
		case 6:  
			$races['Tauren']++;
			break;
		case 7:  
			$races['Gnom']++;
			$ally['Alliance']++;
			break;
		case 8:  
			$races['Troll']++;
			break;
		case 10:	
			$races['Blood Elf']++;
			break;
		case 11: 
			$races['Draenei']++;
			$ally['Alliance']++;
			break;
			}
	switch 	($char['class'])
		{
		case 1: $classes['Warrior']++; break;
		case 2: $classes['Paladin']++; break;
		case 3: $classes['Hunter']++; break;
		case 4: $classes['Rogue']++; break;
		case 5: $classes['Priest']++; break;
		case 7: $classes['Shaman']++; break;
		case 8: $classes['Mage']++; break;
		case 9: $classes['Warlock']++; break;
		case 11: $classes['Druid']++; break;
		}
	}
	@mysql_free_result($result1);

	@mysql_free_result($result);
	$Cache->write('characters',serialize(array('classes'=>$classes,'ally'=>$ally,'races'=>$races,'c'=>$c)));
	mysql_close($char_link);
}
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

$db_info=array();
$Cache->open("./Cache/MySQL",NULL);
$db_info = $Cache->read('account');
if(!empty($db_info))
	$db_info=unserialize($db_info);
if(is_array($db_info))
{
	extract($db_info,EXTR_OVERWRITE);
}else{
	$login_link=@mysql_connect($_CONFIG['MySQL_login_host'],$_CONFIG['MySQL_login_user'],$_CONFIG['MySQL_login_password'],true) or trigger_error("MySQL Err<br> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
	if(!@mysql_select_db($_CONFIG['MySQL_login_db'],$login_link))
	 trigger_error("MySQL Err<br> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
	$result=mysql_query("SELECT count(*) FROM `accounts`",$login_link);
	$count_acc=mysql_result($result,0);
	@mysql_free_result($result);
	$result=mysql_query("SELECT count(*) FROM `accounts` WHERE `gm` NOT LIKE ''",$login_link);
	$count_gm=mysql_result($result,0);
	$Cache->write('account',serialize(array('count_acc'=>$count_acc,'count_gm'=>$count_gm)));
	@mysql_free_result($result);
	mysql_close($login_link);
}
unset($db_info);

if(@$_GET['do']!='reg')
{
	$tpl->setParam("FormReg_msg","");
	$tpl->setParam('FormReg_user',"");
	$tpl->setParam('FormReg_password',"");
	$tpl->setParam('FormReg_email',"");
}else{
	$login_link=@mysql_connect($_CONFIG['MySQL_login_host'],$_CONFIG['MySQL_login_user'],$_CONFIG['MySQL_login_password'],true) or trigger_error("MySQL Err<br> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
	if(!@mysql_select_db($_CONFIG['MySQL_login_db'],$login_link))
	 trigger_error("MySQL Err<br> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
	$ip = getenv('REMOTE_ADDR');
	$isLastIP = mysql_result(mysql_query("SELECT count(`lastip`) FROM `accounts`  WHERE `lastip`='$ip'",$login_link),0);
	if(!eregi("^[a-z,A-Z]", @$_POST['reg_password']) OR !eregi("^[a-z,A-Z]", @$_POST['reg_name']) OR $isLastIP>=$_CONFIG['max_acc_per_ip'] OR (strlen(@$_POST['reg_password'])<6 OR strlen(@$_POST['reg_password'])>32) OR (strlen(@$_POST['reg_name'])<4 OR strlen(@$_POST['reg_name'])>16) OR !Net_CheckIP::check_ip($ip) OR !string_isEmail(@$_POST['reg_email']))
	{
		$body="<center>Error:</center><br>";
		if(!eregi("^[a-z,A-Z]", @$_POST['reg_name'])){$body.="- Login must contain <b>ONLY</b> low or upper letters<br>";}
		if(!eregi("^[a-z,A-Z]", @$_POST['reg_password'])){$body.="- Password must contain <b>ONLY</b> low or upper letters<br>";}
		if(strlen(@$_POST['reg_password'])<6 OR strlen(@$_POST['reg_password'])>32){$body.="- Password must be 6 letters minimum<br>";}
		if(strlen(@$_POST['reg_name'])<4 OR strlen(@$_POST['reg_name'])>16){$body.="- Login must contain from 4 to 16 letters<br>";}
		if($isLastIP>=$_CONFIG['max_acc_per_ip']){$body.="- There is only ".$_CONFIG['max_acc_per_ip']." account per one ip address ({$isLastIP})<br>";}
		if(!Net_CheckIP::check_ip($ip)){$body.="- you have invalid ip";}
		if(!string_isEmail(@$_POST['reg_email'])){$body.="- you have invalid email";}
		$tpl->setParam("FormReg_msg",$body);
		$tpl->setParam('FormReg_user',@$_POST['reg_name']);
		$tpl->setParam('FormReg_password',"");
		$tpl->setParam('FormReg_email',@$_POST['reg_email']);
	}else{

		$ts=CheckPassword($_POST['reg_name'],$_POST['reg_password']);
		if(!$ts[0]){
			$tpl->setParam("FormReg_msg","<center>Error:</center><br>".$ts[1]);
			$tpl->setParam('FormReg_user',$_POST['reg_name']);
			$tpl->setParam('FormReg_password',"");
			$tpl->setParam('FormReg_email',$_POST['reg_email']);
		}else{
			$username = htmlspecialchars(trim($_POST['reg_name']));
			$passw = trim($_POST['reg_password']);
			$email = htmlspecialchars(trim($_POST['reg_email']));
			if(@mysql_query("INSERT INTO `accounts` (`login`,`password`,`lastip`,`email`,`flags`) VALUES ('$username','$passw','$ip','$email','8')",$login_link))
			{
				$Cache->open("./Cache/MySQL",NULL);
				$Cache->destroy('account');
				$result=mysql_query("SELECT count(*) FROM `accounts`",$login_link);
				$count_acc=mysql_result($result,0);
				@mysql_free_result($result);
				$result=mysql_query("SELECT count(*) FROM `accounts` WHERE `gm` NOT LIKE ''",$login_link);
				$count_gm=mysql_result($result,0);
				$Cache->write('account',serialize(array('count_acc'=>$count_acc,'count_gm'=>$count_gm)));
				mysql_free_result($result);
				$tpl->setParam("FormReg_msg","Account '{$username}' Created");
				$tpl->setParam('FormReg_user',"");
				$tpl->setParam('FormReg_password',"");
				$tpl->setParam('FormReg_email',"");
			}else{
				$tpl->setParam("FormReg_msg","Account '{$username}' Failed");
				$tpl->setParam('FormReg_user',@$_POST['reg_name']);
				$tpl->setParam('FormReg_password',"");
				$tpl->setParam('FormReg_email',@$_POST['reg_email']);
			}
		}
	}	
	mysql_close($login_link);
}
$tpl->setParam('S_1',number_format($count_acc));
$tpl->setParam('S_6',number_format($count_gm));
if(!$_CONFIG['logo']){
	$tpl->setParam('PAGE_logo',"");
}else{
	$tpl->setParam('PAGE_logo',"<img src=\"{$_CONFIG['logo']}\">");
}

$tpl->setParam('PS_INFO',"Gzip {$gzpstatus}, time build page ".round($MTimer->stop(),4)."s");
$tpl->parseParam('page');
$tpl->printParam('page',true);
?>