<?php

//if (!extension_loaded('mysql')) {
//	die("MYSQL Extension not found");
//}
if(getenv('REMOTE_ADDR')=="127.0.0.1")ini_set('track_errors', TRUE);


require_once './inc/basic.inc.php';
$MTimer = new MTimer;
$MTimer->start();
require_once './inc/object.inc.php';
require_once './inc/template.inc.php';
require_once './inc/sgfiles.inc.php';
require_once './inc/file.inc.php';
require_once './inc/dir.inc.php';
require_once './inc/cache.inc.php';
require_once './inc/module.inc.php';
$_CONFIG=array();
require_once './config.php';
require_once './inc/gzip.inc.php';
require_once './inc/reg.inc.php';
require_once './inc/Pager.php';
require_once './inc/Log.php';

$logger = &Log::singleton('file', './Cache/weblog.log', '', $_CONFIG['log']);
$is_admin=is_admin(getenv('REMOTE_ADDR'));

$Cache->open("./Cache/MySQL",NULL);
$Cache->gc($_CONFIG['statistics_update_time']);
$Cache->open("./Cache/ch_pass_lock",NULL);
$Cache->gc($_CONFIG['blockcp']);
if(!is_array($_CONFIG['stats.xml']))$_CONFIG['stats.xml']=array($_CONFIG['stats.xml']);

$xml_time=array();
foreach($_CONFIG['stats.xml'] as $k => $v){
	if(@$_GET['m'] == "onl" OR $k==0){
	if(substr($v,0,5) == "http:")
	{
		if(!ini_get("allow_url_fopen"))
			die("Error, HTTP_File: \"{$v}\"\n<br>\n<center>to enable this option you must set allow_url_fopen=1 in php.ini</center>");
		$h1=@get_headers($v, 1);
		if(!is_array($h1)){
			if($k<>0){
				echo("<-- Error - skip, HTTP_File({$k}): \"{$v}\" not exists -->");
				unset($_CONFIG['stats.xml'][$k]);
				if(isset($_CONFIG['serv_names'][$k]))
				unset($_CONFIG['serv_names'][$k]);
			}else{
				die("Error, HTTP_File: \"{$v}\" not exists");
			}
			
		}
		if(is_array($h1)){
			if(!isset($h1[0]))
				$h2=explode(" ",$h1['status']);
			else
				$h2=explode(" ",$h1[0]);
			if($h2[1]!=200)
				if($k<>0){
					echo("<-- Error - skip, HTTP_File({$k}): \"{$v}\"\n<br>".$h1[0]." -->");
					unset($_CONFIG['stats.xml'][$k]);
					if(isset($_CONFIG['serv_names'][$k]))
					unset($_CONFIG['serv_names'][$k]);
				}else{
					die("Error, HTTP_File: \"{$v}\"\n<br>".$h1[0]);
				}
			if(isset($h1['Last-Modified']))
				$xml_time[$k] = strtotime($h1['Last-Modified']);
			else
				$xml_time[$k] = strtotime($h1['last-modified']);
			unset($h1);
			unset($h2);
		}
	}else{
		$xml_stats=new File($v, 'r');
		if(!$xml_stats->exists())
			die("Error, File: \"{$v}\" not exists");
		//if (!$xml_stats->open()) 
		//	die("Error, File: \"{$v}\" can't read");
		$xml_time[$k]=$xml_stats->getMTime();
		object_checkError($xml_time);
		$xml_stats->close();
	}}
}
extract($Cache->c_get("XML","xml-stats",array('xml_time'=>$xml_time,'_CONFIG'=>$_CONFIG)),EXTR_OVERWRITE);
$tpl= new Template($_CONFIG['tpl']);
$tpl->setFile('page',"main.tpl");
$tpl->parseFile('page');

$mods=new module();
$mods->load();


//-------------------


$mods->getactive(&$tpl);


if(!$_CONFIG['logo']){
	$tpl->setParam('PAGE_logo',"");
}else{
	$tpl->setParam('PAGE_logo',"<img src=\"{$_CONFIG['logo']}\" alt=\"Logo\"/>");
}
$fcss=new File("server_stats.css", 'r');
if($fcss->open()){
	$tpl->setParam('CSS',$fcss->read());
	$fcss->close();
}

$mods->links(&$tpl);
$info=$xml_data[0]['status'];
foreach($info as $key=>$val)
{
	if(isset($val))
	{
		
		if(eregi("^[[:digit:]]", $val) AND !strpos($val,".") AND !strpos($val," ")){
			settype($val, "integer");
			$val=number_format($val);
		}

		$tpl->setParam(strtolower($key),$val);
	}
}
$tpl->setParam('PS_INFO',"Gzip {$gzpstatus}, time build page ".round($MTimer->stop(),4)."s<br />
					<a href=\"http://validator.w3.org/check/referer\" target=\"_blank\"><img id=\"valid-xhtml10\" src=\"icon/valid-xhtml10.gif\" alt=\"Valid XHTML 1.0!\" width=\"54\" height=\"20\" style=\"margin: 5px 16px;\" /></a>
					<a href=\"http://jigsaw.w3.org/css-validator/check/referer\" target=\"_blank\"><img id=\"valid-css\" src=\"icon/valid-css.gif\" alt=\"Valid CSS!\" width=\"54\" height=\"20\" style=\"margin: 5px 16px;\" /></a>");
$tpl->parseParam('page');
$tpl->printParam('page',false);
?>