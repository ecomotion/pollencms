<?php
if(!defined("PSiteConfigFile")){
	define("PSiteConfigFile",1);
	require 'pconfigfile.php';

/**
 * Class PConfigFile
 * Allow to manipulate a config file
 * A config file can have a filedesc config file that defines the default parameters.
 *
 */
class PSiteConfigFile extends PConfigFile {
	
	var $tabUserList;
	function PSiteConfigFile($strPath,$fileDescr=false){
		parent::PConfigFile($strPath,$fileDescr);
		$this->tabUserList = false;
	}
	
	function getUsersList(){
		if(is_array($this->tabUserList))
			return $this->tabUserList;
		
		$tabParams = $this->getTabParams();
		$this->tabUserList = array();
		foreach($tabParams as $k => $v){
			if(is_array($v) && strstr($k,'ACCOUNT_')){
				$this->tabUserList[$k]=$v;
			}
		}
		return $this->tabUserList;
	}
	function getHtmlUsersList(){
		$tabUsers = &$this->getUsersList();
		if(sizeof($tabUsers)==0)
			return '';
		$strReturn ='
			<table>
				<tr><td>LOGIN</td><td>TYPE</td></tr>
				{LIST}
			</table>
		';
		$strList='';
		foreach($tabUsers as $k=>$v){
			$strList .= '<tr><td>'.$v['LOGIN'].'</td>'.$v['TYPE'].'</td></tr>'."\n";
		}
		return str_replace('{LIST}',$strList,$strReturn);
	}
}
	
}//END DEFINE
?>