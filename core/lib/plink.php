<?php
if(!defined("PLINK")){
define("PLINK",1);
require('ppage.php');

/**
 * Class PLink.
 * This class is used to manipulate web site link pages.
 *
 */

class PLink extends PPage {
	var $oLinkedPage;	
	
	function PLink($strPath){
		parent::PPage($strPath);
		$strContent = file_get_contents($this->path);
		$this->oLinkedPage = getFileObjectAndFind(PAGES_PATH.SLASH.$strContent);
		
	}
	
	/**
	 * @return string the id of the page, it is in fact the url without the /
	 */
	function getId(){
		return str_replace('.lnk','',str_replace('/','slash',str_replace(SITE_URL,'',$this->getUrl(true))));
	}
	
	function menuEdit($url){
		
		return ($this->oLinkedPage)?$this->oLinkedPage->menuEdit($url):false;
	}
	
	function setLinkedPage(&$oPage){
		if(!$this->Save($oPage->getRelativePath(PAGES_PATH)))
			return false;
		$this->oLinkedPage = $oPage;
		return true;
	}
			
	function getUrl(){
		return ($this->oLinkedPage)?$this->oLinkedPage->getUrl():false;
	}
	
	function getDisplayUrl($proot_dir=false){
		return ($this->oLinkedPage)?$this->oLinkedPage->getDisplayUrl($proot_dir):false;
	}
	

}//end class

}//end define

?>