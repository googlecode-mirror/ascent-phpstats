<?php

class Auth_Container_BZ extends Auth_Container{
    function Auth_Container_BZ()
    {
    
    }
    function fetchData($username, $password)
    {
	global $system;
	if (!extension_loaded('mysql')) return false;
	$cuser=mysql_escape_string($username);
	$cpass=mysql_escape_string($password);
	$system->cache->open("./Cache/MySQL",NULL);
	$accinfo = $system->cache->read("gm_".SHA1(strtoupper($username).':'.strtoupper($password)),true);
	if($accinfo) return true;

	$login_link=$system->mysql_login();
	$sql_get_pass="SELECT `accounts`.`encrypted_password` FROM `accounts` WHERE `accounts`.`login` =  '{$cuser}' AND `accounts`.`password` = '{$cpass}' AND (`accounts`.`gm` =  \"a\" OR `accounts`.`gm` =  \"az\")";
	$sq=mysql_query($sql_get_pass,$login_link);
	if(mysql_num_rows($sq)>0){
		$system->cache->write("gm_".SHA1(strtoupper($username).':'.strtoupper($password)),1,true);
		mysql_close($login_link);
		return true;
	}
	return false;
    }
}
?>