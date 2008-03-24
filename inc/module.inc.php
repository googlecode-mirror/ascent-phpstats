<?php
class module extends Object
{
	var $mods=array();
	function module() {
		Object::Object();
	}
	function load(){
		foreach(glob("modules/*.php") as $fname){
			include_once($fname);
			$cname=explode(".",basename($fname));
			$this->reg(object_create("module_".$cname[0]));
		}
	}
	function reg(&$mod){
		if(!$mod->isload())return 0;
		$mod_name=$mod->getlink();
		if($mod_name=="NULL")return 0;
		$this->mods[$mod_name]=&$mod;
		return 1;
	}
	function links(&$tpl){
		global $_GET,$_CONFIG,$is_admin;
		$amod = @$_GET['m'];
		$mods=$this->mods;
		$tr="";
		foreach($mods as $key => $val){
			if($val->ismenu() OR $is_admin){
				if($amod==$val->getlink())
				$tr.="<u>".$val->getname()."</u> | ";
				else
				$tr.="<a href=\"?m=".$val->getlink()."\">".$val->getname()."</a> | ";
			}
		}
		$tpl->setParam('MOD_LINKS',substr($tr,0,strlen($tr)-2));
	}
	function getactive(&$tpl){
		global $_GET,$_CONFIG;
		if(isset($this->mods[@$_GET['m']])){
			$tpl->setFile('MOD_PAGE',$this->mods[@$_GET['m']]->gettplfile().".tpl");
			$tpl->parseFile('MOD_PAGE');
			$tpl->setParam('MOD_NAME',$this->mods[@$_GET['m']]->getname(&$tpl));
			$this->mods[@$_GET['m']]->getdata(&$tpl);
			return 1;
		}else{
			$tpl->setFile('MOD_PAGE',$this->mods['']->gettplfile().".tpl");
			$tpl->parseFile('MOD_PAGE');
			$tpl->setParam('MOD_NAME',$this->mods['']->getname(&$tpl));
			$this->mods['']->getdata(&$tpl);
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