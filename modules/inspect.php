<?php 
class module_inspect extends module_obj
{
	var $sql;
	function module_inspect(){
		
	}
	function ismenu(){
		return 0;
	}
	function isload(){
		global $is_admin,$system,$_CONFIG;
		if(!is_array($system->sl) and !isset($system->sl[$system->as])) $srv=$system->sl[0]['mysql'];
		else
		$chardb=$system->sl[$system->as]['mysql'];
		if($chardb["host"]!=@$_CONFIG['MySQL_world_host'] OR $chardb["user"]!=@$_CONFIG['MySQL_world_user'])
		return 0; // not supported multi MySQL Server

		if(!$is_admin) return 0;

		return 1;
	}
	function getname(){
		return "inspect";
	}
	function sort_item_list($a,$b){
		if (@$a['quality'] == @$b['quality']){ return 0; } 
		return (@$a['quality'] > @$b['quality']) ? -1 : 1;
	}
	function build_item_list($guid,$char_link,$world_link){
		global $system,$_CONFIG;
		if(!isset($_SESSION['item_cache']) or !is_array($_SESSION['item_cache']))
		$_SESSION['item_cache']=array();

		$data=array();
		$sql_1="SELECT * FROM `playeritems` as `data` WHERE `ownerguid` =  {$guid}";
		$sql_2="SELECT * FROM `items` as `info` WHERE `entry` = ";

		$sq2=mysql_query($sql_1,$char_link);
		$data[$guid]=array();
		while ($dr2 = mysql_fetch_array($sq2))
		{
			if(empty($_SESSION['item_cache'][$dr2['entry']])){
				$sq3=mysql_query($sql_2.$dr2['entry'],$world_link);
				$dr3 = mysql_fetch_array($sq3);
				$_SESSION['item_cache'][$dr2['entry']]=$system->cleanup_array_mysql($dr3);
				$item=$dr3;
				@mysql_free_result($sq3);
			}else
				$item=$_SESSION['item_cache'][$dr2['entry']];
			if(is_array($item) and count($item)>0)
			$data[$guid][]=array_merge($system->cleanup_array_mysql($dr2),$item);
		}
		@mysql_free_result($sq2);
		usort($data[$guid],array("module_inspect","sort_item_list"));
		return $data;
	}
	function getlink(){
		return "inspect";
	}
	function getdata(&$tpl){
		global $_CONFIG,$_POST,$_GET,$system;
		require_once './inc/base.inc.php';
		
		if(($_SERVER["REQUEST_METHOD"]=="POST" AND isset($_POST) AND isset($_POST['c_user'])) OR isset($_GET['char'])){
			if(isset($_GET['char'])) $_POST['c_user']=$_GET['char'];
			$tpl->setParam('c_user',$_POST['c_user']);
			if(strlen($_POST['c_user'])<2){
				$tpl->setParam('c_msg','<center>Error:</center><br />'.'Account name... INVALID!');
				return;}
			$c_sql_user=mysql_escape_string($_POST['c_user']);
			$char_link=$system->mysql_connect();
			$sq=mysql_query("SELECT `characters`.`acct`, `characters`.`guid`, `characters`.`name` FROM `characters` WHERE `characters`.`name` =  '{$c_sql_user}'",$char_link);
			if(mysql_num_rows($sq)==0){
				$tpl->setParam('c_msg',"<center>Error:</center><br />"."Char not exist");
				mysql_close($char_link);
				return;
			}
			$dr=mysql_fetch_array($sq);
			$world_link=$system->mysql_world();
			$data=array();
			$data=$this->build_item_list($dr['guid'],$char_link,$world_link);
			
			mysql_close($world_link);
			mysql_close($char_link);

			$msg="Done! for char {$_POST['c_user']}<br/>\n ";

			foreach($data as $acc => $itemlist){
				$msg.="<hr/><h2>Char: {$acc}</h2>\n";
				foreach($itemlist as $item){
					if($item['quality']==0)$msg.="[color=grey]";
					elseif($item['quality']==2)$msg.="[color=green]";
					elseif($item['quality']==1)$msg.="[color]";
					elseif($item['quality']==3)$msg.="[color=blue]";
					elseif($item['quality']==4)$msg.="[color=purple]";
					$msg.="{$item['name1']} ({$item['entry']})[/color]<br/>[url=http://thottbot.com/i{$item['entry']}]thottbot[/url]&nbsp;&nbsp;[url=http://www.wowhead.com/?item={$item['entry']}]wowhead[/url]<br/>\n";
					if(!empty($item['description']))$msg.="[color=gold]{$item['description']}[/color]<br/>\n";
					$msg.="containerslot: {$item['containerslot']}<br/>";
					$msg.="slot: {$item['slot']}<br/>";
					$msg.="Count: {$item['count']}/{$item['maxcount']}<br/>\n";
					$msg.="<br/><br/>\n";
				}
				$options = &PEAR::getStaticProperty('HTML_BBCodeParser', '_options');
				$options['filters']="Basic,Extended,Images,Links,Lists,Email";
				unset($options);
				//$msg.=nl2br(print_r($_SESSION['item_cache'],1));
			}
			$tpl->setParam('c_msg',HTML_BBCodeParser::staticQparse($msg));
		}else{
			$tpl->setParam('c_msg','');
			$tpl->setParam('c_user','');
		}
	}
	function gettplfile(){
		return "lookup";
	}
}
?>