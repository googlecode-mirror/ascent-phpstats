<?php
class module extends Object
{
	var $mods=array();
	function module() {
		Object::Object();
	}
	function load(){
		foreach(glob("modules/*.php") as $fname){
			$cname=explode(".",basename($fname));
			include_once($fname);
			$this->reg(object_create("module_".$cname[0]));
		}
	}
	function reg(&$mod){
		global $_CONFIG;
		if(!$mod->isload())return 0;
		$mod_name=$mod->getlink();
		if($mod_name==$_CONFIG['main_module']) $mod_name="";
		if($mod_name=="NULL")return 0;
		$this->mods[$mod_name]=&$mod;
		return 1;
	}
	function getactive(&$tpl){
		global $_GET,$_CONFIG;
		if(isset($this->mods[@$_GET['m']]) AND !is_null(@$_GET['m']) AND strlen(@$_GET['m'])>0 AND strlen(@$_GET['m'])<9){
			$tpl->setFile('MOD_PAGE',$this->mods[@$_GET['m']]->gettplfile().".tpl");
			$tpl->parseFile('MOD_PAGE');
			$tpl->setParam('MOD_NAME',$this->mods[@$_GET['m']]->getname(&$tpl));
			$this->mods[@$_GET['m']]->getdata(&$tpl);
			$tpl->setParam('mlnk',@$_GET['m']);
			return 1;
		}else{
			$tpl->setFile('MOD_PAGE',$this->mods['']->gettplfile().".tpl");
			$tpl->parseFile('MOD_PAGE');
			$tpl->setParam('MOD_NAME',$this->mods['']->getname(&$tpl));
			$this->mods['']->getdata(&$tpl);
			$tpl->setParam('mlnk','');
			return 0;
		}
	}
}
class module_obj
{
	function isload(){
		return 0;
	}
	function ismenu(){
		return 0;
	}
	function getname(){
		return "NULL";
	}
	function getdata(){
		return "NULL";
	}
	function getlink(){
		return "NULL";
	}
	function gettplfile(){
		return "NULL";
	}
}
?>