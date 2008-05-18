<?php 
class module_online extends module_obj
{
	var $serv=0;
	function ismenu(){
		return 1;
	}
	function isload(){
		return 1;
	}
	function getname(){
		return "Online";
	}
	function getlink(){
		return "onl";
	}
	function getdata(&$tpl){
		global $system,$_CONFIG,$base_maptype,$base_class,$base_race,$base_faction,$base_map,$base_areaid,$is_admin;
require_once './inc/base.inc.php';
$tpl->setParam('OPT_SLIST','');
$tpl->setBlock('MOD_PAGE', 'Inst');
$tpl->setBlock('MOD_PAGE', 'GM');
$tpl->setBlock('MOD_PAGE', 'PL');
for($i=1;$i<=11;$i++)
{
	$tpl->setParam('so_'.$i,0);
}
if(isset($_GET) AND isset($_GET['mord'])){
	if($_GET['mord']=="map,0")
	$tpl->setParam('so_1',1);
	elseif($_GET['mord']=="players,0")
	$tpl->setParam('so_2',1);
	elseif($_GET['mord']=="maxplayers,0")
	$tpl->setParam('so_3',1);
	elseif($_GET['mord']=="maptype,0")
	$tpl->setParam('so_4',1);
}
if(isset($_GET) AND isset($_GET['pord'])){
	if($_GET['pord']=="name,0")
	$tpl->setParam('so_5',1);
	elseif($_GET['pord']=="race,0")
	$tpl->setParam('so_6',1);
	elseif($_GET['pord']=="class,0")
	$tpl->setParam('so_7',1);
	elseif($_GET['pord']=="level,0")
	$tpl->setParam('so_8',1);
	elseif($_GET['pord']=="map,0")
	$tpl->setParam('so_10',1);
	elseif($_GET['pord']=="areaid,0")
	$tpl->setParam('so_11',1);
}

if(is_array(@$system->xml['instances']))
{
	$list_inst=@$system->xml['instances']['instance'];
}else{
	$list_inst=array();
}
if(!is_array($list_inst) OR count($list_inst)<1)
{
	$list_inst=array();
}else{
	if(!isset($list_inst[0])){
		$list_inst=array(0=>$list_inst);
	}
	usort($list_inst,"sortbyplayers");
}

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
		$tpl->setParam('Inst_players',number_format($val['players']));
	}else{
		if(!$is_admin){
			if($val['players']>$val['maxplayers'])
			$tpl->setParam('Inst_players',number_format($val['maxplayers']));
			else
			$tpl->setParam('Inst_players',number_format($val['players']));
		}else{
			$tpl->setParam('Inst_players',number_format($val['players']));
		}
		$tpl->setParam('Inst_maxplayers',number_format($val['maxplayers']));
	}


	$tpl->setParam('Inst_creationtime',$val['creationtime']);
	$tpl->setParam('Inst_expirytime',$val['expirytime']);
	$tpl->setParam('Inst_World',$base_maptype[$val['maptype']]);
	$tpl->parseParam('Inst', 'InstDynamic', true);
}
$tpl->setParam('Inst', $tpl->getParam('InstDynamic'));
if(is_array(@$system->xml['gms']))
{
	$list_gm=@$system->xml['gms']['gmplr'];
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
	if(!$_CONFIG['showgm'] and !$is_admin) break;
	if($is_admin)
	$tpl->setParam('GM_name',"<a href=\"?m=lookup&amp;char={$val['name']}\">{$val['name']}</a>");
	else
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
if(is_array(@$system->xml['sessions']))
{
	$list_pl=@$system->xml['sessions']['plr'];
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
	if($is_admin)
	$tpl->setParam('PL_name',"<a href=\"?m=lookup&amp;char={$val['name']}\">{$val['name']}</a>");
	else
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
	}
	function gettplfile(){
		return "online";
	}
}
?>