<?php
//if (!extension_loaded('mysql')) {
//	die("MYSQL Extension not found");
//}
//if(getenv('REMOTE_ADDR')=="127.0.0.1")ini_set('track_errors', TRUE);

$_CONFIG=array();
require_once './config.php';

// Error logger
function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars) 
{
    // define an assoc array of error string
    // in reality the only entries we should
    // consider are E_WARNING, E_NOTICE, E_USER_ERROR,
    // E_USER_WARNING and E_USER_NOTICE
    $errortype = array (
                E_ERROR              => 'Error',
                E_WARNING            => 'Warning',
                E_PARSE              => 'Parsing Error',
                E_NOTICE             => 'Notice',
                E_CORE_ERROR         => 'Core Error',
                E_CORE_WARNING       => 'Core Warning',
                E_COMPILE_ERROR      => 'Compile Error',
                E_COMPILE_WARNING    => 'Compile Warning',
                E_USER_ERROR         => 'User Error',
                E_USER_WARNING       => 'User Warning',
                E_USER_NOTICE        => 'User Notice',
                E_STRICT             => 'Runtime Notice',
                E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
                );
    // set of errors for which a var trace will be saved
    $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);
    if(E_STRICT==$errno OR E_NOTICE==$errno) return;
    require_once dirname(__FILE__).'/inc/Log.php';
    $logger=&Log::singleton('file', dirname(__FILE__).'/Cache/phperr.log', '', array('mode' => 0600, 'timeFormat' => '%X %x'));
    //if (in_array($errno, $user_errors))
    //$logger->log("PHP {$errortype[$errno]}: {$errmsg} in {$filename} on line {$linenum}, ".wddx_serialize_value($vars, "Variables"),PEAR_LOG_CRIT);
    //else
    $logger->log("PHP {$errortype[$errno]}: {$errmsg} in {$filename} on line {$linenum}",PEAR_LOG_WARNING);
}
//error_reporting(0);
$old_error_handler = set_error_handler("userErrorHandler");

if(!is_array($_CONFIG['stats.xml']))$_CONFIG['stats.xml']=array($_CONFIG['stats.xml']);
require_once './inc/basic.inc.php';
$MTimer = new MTimer;
$MTimer->start();
require_once './inc/subsystem.php';
$system=new subsystem();

if(isset($_GET) and isset($_GET['auth']) and $_GET['auth']=="start") $system->auth->start();

$is_admin=is_admin(getenv('REMOTE_ADDR'));

$xml_time=array();
$system->buildservlist();
	if(substr($_CONFIG['stats.xml'][$system->as],0,5) == "http:")
	{
		if(!ini_get("allow_url_fopen"))
			die("Error, HTTP_File: \"{$_CONFIG['stats.xml'][$system->as]}\"\n<br>\n<center>to enable this option you must set allow_url_fopen=1 in php.ini</center>");
		$h1=@get_headers($_CONFIG['stats.xml'][$system->as], 1);
		if(!is_array($h1)){
			die("Error, HTTP_File: \"{$_CONFIG['stats.xml'][$system->as]}\" not exists");
			
		}
		if(is_array($h1)){
			if(!isset($h1[0]))
				$h2=explode(" ",$h1['status']);
			else
				$h2=explode(" ",$h1[0]);
			if($h2[1]!=200)
				die("Error, HTTP_File: \"{$_CONFIG['stats.xml'][$system->as]}\"\n<br>".$h1[0]);
			if(isset($h1['Last-Modified']))
				$xml_time = strtotime($h1['Last-Modified']);
			else
				$xml_time = strtotime($h1['last-modified']);
			unset($h1);
			unset($h2);
		}
	}else{
		$xml_stats=new File($_CONFIG['stats.xml'][$system->as], 'r');
		if(!$xml_stats->exists())
			die("Error, File: \"{$_CONFIG['stats.xml'][$system->as]}\" not exists");
		if (!$xml_stats->open()) 
		$xml_time=-2;
		else
		$xml_time=$xml_stats->getMTime();
		//	die("Error, File: \"{$_CONFIG['stats.xml'][$system->as]}\" can't read");
		object_checkError($xml_time);
		$xml_stats->close();
	}
extract($system->cache->c_get("XML","xml-stats",array('xml_time'=>$xml_time,'_CONFIG'=>$_CONFIG)),EXTR_OVERWRITE);

$tpl= new Template($_CONFIG['tpl']);
$tpl->setFile('page',"main.tpl");
$tpl->parseFile('page');

$system->mods->load();


//-------------------


$system->mods->getactive(&$tpl);


if(!$_CONFIG['logo']){
	$tpl->setParam('PAGE_logo',"");
}else{
	$tpl->setParam('PAGE_logo',"<img width=770 src=\"{$_CONFIG['logo']}\" alt=\"Logo\"/>");
}
$fcss=new File("server_stats.css", 'r');
if($fcss->open()){
	$tpl->setParam('CSS',$fcss->read());
	$fcss->close();
}

$system->links(&$tpl);
$info=@$system->xml['status'];
$default_status=array("platform"=>"","uptime"=>"","oplayers"=>"","cpu"=>"","qplayers"=>"","avglat"=>"","threads"=>"","gmcount"=>"","acceptedconns"=>"","alliance"=>"","peakcount"=>"","horde"=>"","lastupdate"=>"","ram"=>"");
$dis_status=array();
if(!is_array($info)) $info=$default_status;
if(count($info)!=count($default_status)){
	foreach($default_status as $ln=>$row){
		if(!isset($info[$ln])) $info[$ln]=$row;
	}
}
foreach($info as $key=>$val)
{
	$key=strtolower($key);
	if(isset($val))
	{
		if(eregi("^[[:digit:]]", $val) AND !strpos($val,".") AND !strpos($val," ")){
			settype($val, "integer");
			$val=number_format($val);
		}
		if(isset($_CONFIG['dis_status'][$key]))
		$tpl->setParam($key,$_CONFIG['dis_status'][$key]);
		else
		$tpl->setParam($key,$val);
	}elseif($default_status[$key]){
		$tpl->setParam($key,$default_status[$key]);
	}
}
$tpl->setParam('PS_INFO',"Gzip {$gzpstatus}, time build page ".round($MTimer->stop(),4)."s<br />
					<a href=\"http://validator.w3.org/check/referer\" target=\"_blank\"><img id=\"valid-xhtml10\" src=\"icon/valid-xhtml10.gif\" alt=\"Valid XHTML 1.0!\" width=\"54\" height=\"20\" style=\"margin: 5px 16px;\" /></a>
					<a href=\"http://jigsaw.w3.org/css-validator/check/referer\" target=\"_blank\"><img id=\"valid-css\" src=\"icon/valid-css.gif\" alt=\"Valid CSS!\" width=\"54\" height=\"20\" style=\"margin: 5px 16px;\" /></a>");
$tpl->parseParam('page');
$tpl->printParam('page',true);
?>