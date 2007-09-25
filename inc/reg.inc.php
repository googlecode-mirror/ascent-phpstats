<?php
function string_isEmail($string) {
    return ereg("^[^@  ]+@([a-zA-Z0-9\-]+\.)+([a-zA-Z0-9\-]{2}|net|com|gov|mil|org|edu|int)\$", $string);
}
function CheckPassword($login,$password){
	global $_CONFIG;
	$login=strtoupper($login);
	$password=strtoupper($password);
	if(stripos($password,$login) !== false){
		if($_CONFIG['reg_e']>0 AND (strlen($password)==strlen($login) OR strlen($password) >= strlen($login)+$_CONFIG['reg_e']))
			return array(false,"Password is simple");
	}
	$f=file("badpass.php");
	foreach($f as $line=>$data)
	{
		if($line!=0 AND $password==strtoupper($data))
			return array(false,"Password is simple");
	}
	return array(true,NULL);
}
?>