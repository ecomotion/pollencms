<?php

if(!defined("PPLUGINDIR")){
/**
 * Class: PDirCategory
 * Description: The class pdir directory describe a site category. A category is a directory in the site structure.
 * A Category Directory has an ini file attached to it. This ini file descript the category options: visible, name ....
 *
 * Author: Mathieu Vilaplana
 * Date: Jan 2008
 *
 */
 
	define('PPLUGINDIR',1);
	require_once('pdir.php');


class PPluginDir extends PDir{
	
	var $oPluginConfigFileDescr ;
	var $oConfig;
	function PPluginDir($path){
		parent::PDir($path);
		$this->oPluginConfigFileDescr = new PConfigFile($this->path.SLASH.'config.ini');
		$this->oConfig = new PConfigFile(CONFIG_DIR.'plugins'.SLASH.$this->getIdName().'.ini',&$this->oPluginConfigFileDescr);
	}
	
	function getPluginName(){
		$strName = $this->oPluginConfigFileDescr->getDirectParam('NAME','PLUGIN_INFO');
		if(!$strName)
			$strName = $this->getName();
		return $strName;
	}
	
	function getAuthor(){
		$strAuthor = $this->oPluginConfigFileDescr->getDirectParam('AUTHOR','PLUGIN_INFO');
		if(!$strAuthor)
			$strAuthor = _('unknown');
		return $strAuthor;
	}
	function getVersion(){
		$strVersion = $this->oPluginConfigFileDescr->getDirectParam('VERSION','PLUGIN_INFO');
		if(!$strVersion)
			$strVersion = _('unknown');
		return $strVersion;
	}
	function isActivated(){
		global $configFile;
		$strValue=false;
		$configFile->getParam($this->getIdName(),$strValue,'PLUGINS');
		return ($strValue==="true")?true:false;
	}
	
	function toggleActivate($bSave=false){
		global $configFile;
		$strToggleActive = ($this->isActivated())?"false":"true";
		return $configFile->setParam($this->getIdName(),$strToggleActive,'PLUGINS',$bSave);
	}
		
}
}//NOT DEFINED
	
?>