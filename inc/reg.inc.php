<?php
function string_isEmail($string) {
    return ereg("^[^@  ]+@([a-zA-Z0-9\-]+\.)+([a-zA-Z0-9\-]{2}|net|com|gov|mil|org|edu|int)\$", $string);
}
function string_count($str){
	$str_num=0;
	$str_word=0;
	for($i=0;$i<=strlen($str);$i++){
		if(ereg("^[a-zA-Z]",$str{$i})) $str_word++;
		elseif(ereg("^[0-9]",$str{$i})) $str_num++;
	}
	return array($str_num,$str_word);
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
	$cp=string_count($password);
	if(ereg("^[0-9]",$password{0})) return array(false,"Password is simple, the first symbol can not be number");
	if($cp[0]<1) return array(false,"Password is simple, 1 or more numbers required in password");
	if($cp[1]<5) return array(false,"Password is simple, 5 or more characters required in password");

	return array(true,NULL);
}
?>