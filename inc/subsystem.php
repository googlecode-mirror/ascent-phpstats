<?php
require_once dirname(__FILE__).'/object.inc.php';
require_once dirname(__FILE__).'/template.inc.php';
require_once dirname(__FILE__).'/sgfiles.inc.php';
require_once dirname(__FILE__).'/file.inc.php';
require_once dirname(__FILE__).'/dir.inc.php';
require_once dirname(__FILE__).'/Pear.php';
require_once dirname(__FILE__).'/Auth/HTTP.php';
require_once dirname(__FILE__).'/Auth/Container.php';
require_once dirname(__FILE__).'/TEXT/CAPTCHA.php';
require_once dirname(__FILE__).'/HTML/BBCodeParser.php';
class subsystem
{
	var $cache;
	var $log;
	var $info=array();
	var $as=0;
	var $sl=array();
	var $mods;
	var $anticheat=array("\\","\"","'");
	var $auth;
	var $_cache;
	var $valid_str="^[A-Za-z0-9]+$";
	function subsystem(){
		global $_CONFIG,$_POST,$_COOKIE;
		$this->incload();
		$this->log=&Log::singleton('file', dirname(dirname(__FILE__)).'/Cache/weblog.log', '', $_CONFIG['log']);
		$this->_cache=dirname(dirname(__FILE__))."/Cache";
		$this->info=$this->cs();
		if(!$this->info) $this->info=$this->ns();
		$this->mods=new module();
		$this->cache_check();
		$this->setup_auth();
		//var_dump($_COOKIE);
		$this->cache->open("./Cache/ban",NULL);
		$bandata=$this->cache->read(getenv('REMOTE_ADDR'),true);
		if($bandata){
			$bandata=unserialize($bandata);
			$sd_m=round(($bandata['time']-(mktime()-$bandata['tn']))/60,0);
			$sd_h=floor($sd_m/60);
			$sd_m=$sd_m-$sd_h*60;
			if($sd_m<0)$sd_m=0;
			if($sd_h<0)$sd_h=0;
			die("<center><h2>Sorry,but you banned</h2></center><br/><center><h2>ban time left {$sd_h}h {$sd_m}m</h2></center><br/><center><h2>Reason: {$bandata['reason']}</h2></center>");
		}
		if(isset($_POST) AND is_array($_POST)){
			foreach($_POST as $key => $val){
				$v2=str_replace($this->anticheat, "", $val);
				if($v2!=$val){
				$this->log->log("Detected try hack POST from ".getenv('REMOTE_ADDR')." key \"{$key}\" val \"{$val}\"");
				$this->ban(getenv('REMOTE_ADDR'),60*5,"detected invalid post, maybe hack");
				die("detected invalid post, maybe hack, ban time 5m");}
			}
		}
		$config = parse_ini_file(dirname(__FILE__).'/BBCodeParser.ini', true);
		$options = &PEAR::getStaticProperty('HTML_BBCodeParser', '_options');
		$options = $config['HTML_BBCodeParser'];
		$options['open']="[";
	}
	function ban($ip,$time,$reason){
		$this->cache->open("./Cache/ban",NULL);
		$this->cache->write($ip,serialize(array('ip'=>$ip,'time'=>$time,'reason'=>$reason,'tn'=>mktime())),true);
	}
	function is_online($name){
		$xml=@$this->xml['sessions']['plr'];
		if(isset($xml) and !is_array($xml) and !isset($xml[0])){
			$xml=array(0=>$xml);
		}
		foreach($xml as $i=>$data) if($data['name']==$name) return true;
		return false;
	}
	function setup_auth(){
		global $_CONFIG;
		session_save_path(dirname(dirname(__FILE__))."\\Cache\\sid\\");
		@session_start();
		$this->auth=new Auth_HTTP("BZ", array('enableLogging'=>false));
		$this->auth->logger=&Log::singleton('file', dirname(dirname(__FILE__)).'/Cache/sess_log.txt', '', $_CONFIG['log']);
		$this->auth->setRealm('WoW Server', 'sample');
		$this->auth->setIdle(18000); #5 h idle
		//$this->auth->setAdvancedSecurity();
	}
	function links(&$tpl){
		global $_GET,$_CONFIG,$is_admin;
		$amod = @$_GET['m'];
		$mods=$this->mods->mods;
		$tr="<h2>Modules</h2><form>";
		foreach($mods as $key => $val){
			if($val->ismenu() OR $is_admin){
				if($amod==$key)
				$tr.="<input type=\"button\" class=\"button\" value=\"".$val->getname()."\" onClick=\"return false;\" />";
				else
				$tr.="<input type=\"button\" class=\"button\" value=\"".$val->getname()."\" onClick=\"window.location='?m={$key}'\" />";
				//$tr.="<a href=\"?m=".$key."\">".$val->getname()."</a> | ";
			}
		}
		
		$servs=$this->sl;
		if(count($servs)>1){
			$tr.="</form><pre/><h2>Servers</h2>";
			foreach($servs as $id =>$data){
				if($this->as==$id)
				$tr.="<input type=\"button\" style=\"width:120px\" class=\"button\" value=\"{$data['name']}\" onClick=\"return false;\" />";
				//$tr.="<u>{$data['name']}</u> | ";
				else
				$tr.="<input type=\"button\" style=\"width:120px\" class=\"button\" value=\"{$data['name']}\" onClick=\"window.location='?s={$id}&amp;m=".@$_GET['m']."'\" />";
				//$tr.="<a href=\"?s={$id}&amp;m=".@$_GET['m']."\">{$data['name']}</a> | ";
			}
		}
		$tpl->setParam('MOD_LINKS',$tr."</form>");
	}
	function incload(){
		global $_CONFIG;
		$system=$this;
		require_once dirname(__FILE__).'/cache.inc.php';
		require_once dirname(__FILE__).'/module.inc.php';
		require_once dirname(__FILE__).'/gzip.inc.php';
		require_once dirname(__FILE__).'/reg.inc.php';
		require_once dirname(__FILE__).'/Pager.php';
		require_once dirname(__FILE__).'/Log.php';
	}
	function hcache($v=NULL){
		if(is_null($v))
			return $this->cache;
		else{
			$this->cache=$v;
			return $this->cache;
		}
	}
	function buildservlist(){
		global $_CONFIG,$_SESSION,$_GET;
		$list=array();
		if(count($_CONFIG['serv_names'])==count($_CONFIG['stats.xml'])){
			for($i=0;$i<=count($_CONFIG['stats.xml'])-1;$i++){
				if(count($_CONFIG['serv_names'])!=count($_CONFIG['MySQL'])){
				if(is_array($_CONFIG['MySQL'])) $mysql=$_CONFIG['MySQL'][0]; else $mysql=$_CONFIG['MySQL'];
				}else
				$mysql=$_CONFIG['MySQL'][$i];
				$list[$i]=array('name'=>$_CONFIG['serv_names'][$i],'xml'=>$_CONFIG['stats.xml'][$i],'mysql'=>$mysql,'sid'=>$this->info['serv'][$i]['sid']);	
			}
		}
		$this->sl=$list;
		if(count($list)>1){
			if(isset($_GET) AND isset($_GET['s']) AND isset($list[$_GET['s']])){
				//setcookie("SelectedServer", $_GET['s']);
				$_SESSION['SelectedServer']=(int)$_GET['s'];
				//session_commit();
				//print("<h2><center><a href=\"?m=".@$_GET['m']."\">REDIRECTION</a></center></h2>");
				//print("<script type=\"text/javascript\">self.location=\"?m=".@$_GET['m']."\";</script>");
				//exit;
			}
			//var_dump($_SESSION);
			if(isset($_SESSION['SelectedServer']) AND isset($list[$_SESSION['SelectedServer']])) $this->as=$_SESSION['SelectedServer'];
			else $_SESSION['SelectedServer']=0;
		}else{
			$_SESSION['SelectedServer']=0;
		}
	}
	function ns(){
		global $_CONFIG;
		$data=array();
		$data['cs']=count($_CONFIG['stats.xml']);
		$data['serv']=array();
		if(count($_CONFIG['serv_names'])==count($_CONFIG['stats.xml'])){
			for($i=0;$i<=$data['cs']-1;$i++){
				$data['serv'][$i]['name']=$_CONFIG['serv_names'][$i];
				$data['serv'][$i]['sid']=rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
				if(count($_CONFIG['serv_names'])==count($_CONFIG['MySQL']) AND is_array($_CONFIG['MySQL'][$i]))
				$data['serv'][$i]['ismysql']=true;
				else
				$data['serv'][$i]['ismysql']=false;
			}
		}
		$fh=new File("./Cache/sess_system.php","w");
		if($fh->exists()) $fh->delete();
		$fh->open();
		$fh->writeLine('<?php require_once "../config.php"; require_once "../inc/subsystem.php";'); #0
		$fh->writeLine('$system=new subsystem; $system->setup_auth(); $system->auth->start();'); #1
		$fh->writeLine('if(!$system->auth->getAuth()) die("Error 401"); ?>'); #2
		$fh->writeLine(serialize($data)); #3
		return $data;
	}
	function cs(){
		global $_CONFIG;
		$ln=@file("./Cache/sess_system.php");
		if(!is_array($ln) OR !isset($ln[3])) return false;
		$param=@unserialize($ln[3]);
		if((!isset($param['cs']) or $param['cs']==0)) return false;
		if(!isset($param['serv']) OR !isset($param['serv'][0]) OR !is_array($param['serv'][0])) return false;
		$data=$param['serv'];
		unset($data[0]);
		foreach($data as $k => $v){
			if(empty($v) OR !is_array($v)) unset($param['serv'][$k]);
			if($_CONFIG['serv_names'][$k]!=$param['serv'][$k]['name']) return false;
		}
		return $param;
	}
	function mysql_login(){
		global $_CONFIG;
		$login_link=@mysql_connect($_CONFIG['MySQL_login_host'],$_CONFIG['MySQL_login_user'],$_CONFIG['MySQL_login_password'],true) or trigger_error("MySQL Err<br /> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
		if(!@mysql_select_db($_CONFIG['MySQL_login_db'],$login_link))
		 trigger_error("MySQL Err<br /> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
		return $login_link;
		
	}
	function mysql_world(){
		global $_CONFIG;
		$world_link=@mysql_connect($_CONFIG['MySQL_world_host'],$_CONFIG['MySQL_world_user'],$_CONFIG['MySQL_world_password'],true) or trigger_error("MySQL Err<br /> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
		if(!@mysql_select_db($_CONFIG['MySQL_world_db'],$world_link))
		 trigger_error("MySQL Err<br /> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
		return $world_link;
		
	}
	function mysql_connect(){
		if(!is_array($this->sl) and !isset($this->sl[$this->as])) $srv=$this->sl[0]['mysql'];
		else
		$srv=$this->sl[$this->as]['mysql'];
		$char_link=@mysql_connect($srv["host"],$srv["user"],$srv["password"],true) or trigger_error("MySQL Err<br> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
		if(!@mysql_select_db($srv["db"],$char_link))
		 trigger_error("MySQL Err<br> ".mysql_errno() . ": " . mysql_error() . "\n", E_USER_ERROR);
		return $char_link;
	}
	function cleanup_array_mysql($arr){
		if(!is_array($arr) or count($arr)<1) return $arr;
		foreach($arr as $k => $v)
			if($k>0 and $k<32676) unset($arr[$k]);
		return $arr;
	}
	function appysid($id){
		if(!is_array($this->sl) and !isset($this->sl[$this->as]['sid']) and !is_array($this->sl[$this->as])) return $id;
		if(!isset($this->sl[$this->as])) return $id;
		$srv=$this->sl[$this->as];
		return $id.$srv['sid'];
	}
	function cache_check(){
		global $_CONFIG;
		$this->cache->open(dirname(dirname(__FILE__))."/Cache/MySQL",NULL);
		$this->cache->gc($_CONFIG['statistics_update_time']);
		$this->cache->open(dirname(dirname(__FILE__))."/Cache/ch_pass_lock",NULL);
		$this->cache->gc($_CONFIG['blockcp']);
		$this->cache->open(dirname(dirname(__FILE__))."/Cache",NULL);
		$this->cache->gc((60*60)*12); #12h keep secret data :P
		$this->cache->open(dirname(dirname(__FILE__))."/Cache/sid",NULL);
		$this->cache->gc((60*60)*3);
		$this->cache->open(dirname(dirname(__FILE__))."/Cache/ban",NULL);
		$this->cache->gc2((60*60)*24); #if ban file can't read then unban after 24h
	}
}
?>