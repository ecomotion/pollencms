<?php
if(!defined("PDIRCATEGORY")){
/**
 * Class: PDirCategory
 * Description: The class pdir directory describe a site category. A category is a directory in the site structure.
 * A Category Directory has an ini file attached to it. This ini file descript the category options: visible, name ....
 *
 * Author: Mathieu Vilaplana
 * Date: Jan 2008
 *
 */
 
	define('PDIRCATEGORY',1);
	require_once('pdir.php');


class PDirCategory extends PDir{
	
	var $oPConfigFile ;

	function PDirCategory($path){
		parent::PDir($path);
		$this->oPConfigFile = new PConfigFile(dirname($this->path).SLASH.basename($this->getName()).".ini",CONFIG_FILE);

	}
	
	/**
	 * getMenuSpecificsItems,
	 * allow to add some menu items in the right click of the menu.
	 * Todo: plug a pollen event on it
	 *
	 * @return a string with specifics menu items
	 */
	function getMenuSpecificsItems(){
		$strMenu=parent::getMenuSpecificsItems();
		
		//create a link
		$strLabel = _('Create Link');
		$strMenu .= "\n".'<li><a href="javascript:createLink(\''.urljsencode($this->getRelativePath()).'\');" id="hideMenu" >'.$strLabel.'</a></li>';
		
		//show hide in menu
		$bVisible = $this->isShowInMenu();
		$strLabel = $bVisible?_('Hide'):_('Show');
		$strVisible = $bVisible?'false':'true';
		$strMenu .= '<li><a href="javascript:setPageConfigVar(\''.urljsencode($this->getRelativePath()).'\',\'SHOW_IN_MENU\','.$strVisible.');" id="hideMenu" >'.$strLabel.'</a></li>';

		$bPublished = $this->isPublished();
		$strLabel = $bPublished?_('Unpublish'):_('Publish');
		$strPublished = $bPublished?'false':'true';
		$strMenu .= "\n".'<li><a href="javascript:setPageConfigVar(\''.urljsencode($this->getRelativePath()).'\',\'PUBLISHED\','.$strPublished.');" id="hideMenu" >'.$strLabel.'</a></li>';
		
		return $strMenu;
	}

	function Display($thumb_size,$print=false,$url=false,$proot_dir=false){
		echo "<dl class=\"folder file\" id=\"filename=".str_replace(SLASH,'/',$this->getRelativePath())."\">\n";
		$this->DisplayMenu((($proot_dir)?$proot_dir->getRelativePath():''));
		if(!$url) $url=$this->getDisplayUrl($proot_dir);

		$strSpanClass = (!$this->isShowInMenu())?' class="notvisible"':' class="pagevisible"';
		$strSpanClass = (!$this->isPublished())?' class="draft"':$strSpanClass;
		
		echo "\t<dt><a href='$url'>";
		echo "<img align=\"top\"  src='".$this->getMimeIconUrl($print)."' alt=''  ";
		if(defined("EDIT_FILE"))
			echo " id=\"context_menu_".$this->getIdName()."\" ";
	
		echo "/></a></dt>\n";
		echo "\t<dd>";
			echo '<span '.$strSpanClass.'>'.((!$print)?$this->getPrintedName():$print).'</span>';
		echo "</dd>\n";
		echo "</dl>\n";
	}
	
	function getDisplayUrl($proot_dir){
		return $_SERVER["PHP_SELF"]."?current_dir=".urlencode($this->getRelativePath((($proot_dir)?$proot_dir->path:SITE_PATH))).(($proot_dir)?"&rootpath=".urlencode($proot_dir->getRelativePath()):'');
	}
	
	function listDir($options=0,$fullpath=true,$filter=".*",$filterFalse=false,$nofilter=false){
		$tabList = parent::listDir($options,$fullpath,$filter,$filterFalse,$nofilter);		
		
		//order by menu order
		$tabOrdered = Array();
		foreach($tabList as $aFile){
			$oFileFilter = new PFile($this->path.SLASH.basename($aFile));
			$iOrder = 100;
			if($oFileFilter->is_page() || $oFileFilter->is_dircategory()  || $oFileFilter->is_link() ){
				$oPage = &getFileObject($oFileFilter->path);
				$iOrder = $oPage->getMenuOrder();
			}
			$strOrder = (strlen(''+$iOrder)<2)?'0'.$iOrder:''.$iOrder;
			$tabOrdered[$strOrder.'_'.basename($aFile)]=$aFile;
		}
		ksort($tabOrdered);
		$tabReturn = Array();
		foreach($tabOrdered as $k=>$aFile){
			$tabReturn[]=$aFile;
		}
		return $tabReturn;
	}
	
	
	/**
	 * Bescause we manage special chars in the ini file,
	 * we do not need any more restrictions on directories
	 *
	 * @return true
	 */		
	function checkName($strName=false){
		if(strstr(SLASH,$strName) !== false)
			return setError(SLASH.' char is not allowed for security issue');
		return true;
	}
	
	function getId(){
		return str_replace('/','slash',preg_replace('/^'.preg_quote(SITE_URL,'/').'/','',$this->getUrl(true)));
	}
	
	function getUrl($bUseUrlRewriting = true){
		$strPath = $this->getRelativePath();
		//return the path from the language path
		$strPath = preg_replace('/'.str_replace('/','\/',quotemeta(SITE.SLASH.PAGES.SLASH).'[a-zA-Z]{2,}'.quotemeta(SLASH)).'/','',$strPath);
		$strPath = strtolower($strPath);
		if($bUseUrlRewriting){
			$strPath = str_replace(' ','-',$strPath);
			$strPath = str_replace(rawurlencode(SLASH),'/',rawurlencode($strPath)).'/';
		}else{
			$strPath = rawurlencode($strPath);
		}
		return SITE_URL.(($bUseUrlRewriting)?'':'?page=').$strPath;
	}
	
	function getPrintedName(){
		return $this->getVirtualName();
	}
	
	function menuEdit($url=""){
		return "<li><a href=\"admin_file_editor.php?file=".urlencode($this->oPConfigFile->getRelativePath())."\" id=\"edit\" title=\""._("Options")."\">"._("Options")."</a></li>";
	}

	function menuEditContent($url=''){
		$oIndexFile = new PPage($this->path.SLASH.'/'.'index.html');
		//search index file in the category
		$tabIndex = $this->listDir($this->ONLY_FILES,$fullpath=true,$filter='index\.htm[l]?$');
		if(sizeof($tabIndex) > 0){
			$oIndexFile = new PPage($tabIndex[0]);
		}
		
		return "<li><a href=\"admin_file_editor.php?file=".urlencode($oIndexFile->getRelativePath())."\" id=\"edit\" title=\""._("Edit")."\">"._("Edit")."</a></li>";	
	}
	
	
	function getConfig($strParam){
		if($this->oPConfigFile->getParam($strParam,$strValue))
			return $strValue;
		return false;
	}

	function isShowInMenu(){
		if($this->getConfig("SHOW_IN_MENU") === 'false') return false;
		return true;
	}
	
	function isPublished(){
		if($this->getConfig("PUBLISHED")==='false') return false;
		return true;
	}

	function getTemplateName(){
		if($this->path == PAGES_PATH) {global $configFile; return $configFile->getDirectParam('DEFAULT_TEMPLATE');}
		$param = $this->getConfig('TEMPLATE');
		if( $param == 'AS_PARENT' || !$param || empty($param) ){
			$oParentCat = new PDirCategory($this->getParentPath());
			return $oParentCat->getTemplateName();
		}
		return $param;
	}
	
	/**
	 * Return the menu name. Read the configfile and return the MenuTitle.
	 * If not exists, return the name of the file
	 *
	 * @return string, the menu name
	 */
	function getMenuName(){
		$this->oPConfigFile->getParam("MENU_TITLE",$strConfigName);
		if( $strConfigName!="" && $strConfigName != false ) return $strConfigName;
		return $this->getName();	
	}
	
	/**
	 * Return the menu name. Read the configfile and return the MenuTitle.
	 * If not exists, return the name of the file
	 *
	 * @return string, the menu name
	 */
	function getVirtualName(){
		$this->oPConfigFile->getParam("VIRTUAL_NAME",$strConfigName);
		if( $strConfigName!="" && $strConfigName != false ) return $strConfigName;
		return $this->getName();	
	}
	
	/**
	 * Set the menu name in the config.ini file.
	 *
	 * @param string strNewName, the new name to set
	 * @return true if succeed, else return false
	 */
	function setVirtualName($strNewName){		
			
		if(!$this->oPConfigFile->setParam('VIRTUAL_NAME',$strNewName))
			return false;

		if(!$this->oPConfigFile->Save())
			return false;
			
		return true;
	}
		
	/**
	 * getMenuOrder
	 *
	 * @return integer the menu order
	 */
	function getMenuOrder(){
		if($strOrder = $this->getConfig('MENU_ORDER'))
			return intVal($strOrder);
		return 99;
		
	}

	/**
	 * setMenu Order
	 *
	 * @param integer $iOrder
	 * @return true, if succeed
	 */
	function setMenuOrder($iOrder){				
		$iCurrOrder = $this->getMenuOrder();
		//if page order has not been changed do not change it in the ini file
		if($iCurrOrder == $iOrder) return true;
		
		$strOrder = (strlen(''+$iOrder)<2)?('0'.$iOrder):(''.$iOrder);
		
		if(!$this->oPConfigFile->setParam('MENU_ORDER',$strOrder))
			return false;
		
		if(!$this->oPConfigFile->Save())
			return false;
			
		return true;
	}
	
	function Rename($newname, $destDir=false){
		$fileNewName = $this->getUnixName($newname);
		$pageNewName = $newname;
		$pageCurrName = $this->getVirtualName();
		$fileCurrName = $this->getName();
		
		if(strlen($fileNewName)==0) return setError(_("Can not rename with empty name"));
		if($fileNewName == $fileCurrName && $pageCurrName == $fileCurrName && $destDir === false) return false;
		
		//set the menu name in the ini file if needed
		if( !$destDir && !$this->setVirtualName($pageNewName) )
			return false;
			
		//if the directory has changed (move in an other one, or rename)
		if( $fileCurrName != $fileNewName || $destDir !== false ){
			//Change the virtual name
			if(!$destDir && !$this->oPConfigFile->setParam('VIRTUAL_NAME',$pageNewName))
				return false;
			if(!$this->oPConfigFile->Save())
				return false;
			//rename the config file if exists
			if(is_file($this->oPConfigFile->path)){
				$oFileTmp=new PFile($fileNewName);
				if(!$this->oPConfigFile->Rename($oFileTmp->getNameWithoutExt().".ini",$destDir))
					return false;
			}
			//delete the menu cache
			if(!deleteMenuCache()) return false;
			if(!parent::Rename($fileNewName, $destDir)) return false;
		}
		return true;
	}
	
	function Copy($strNewName,$strParentPath=false){
		$strCPageName = $strNewName;
		$strCDirName = $this->getUnixName($strNewName);
		
		if(strlen($strCDirName)==0) return setError(_("Can not copy page with empty name."));
		if( !$this->checkname($strCPageName) ) return false;
		if(!$strParentPath) $strParentPath=$this->getParentPath();
		
		$strCDirPath = $strParentPath.SLASH.$strCDirName;
		
		if ( getFileObjectAndFind($strCDirPath) )
			return setError(sprintf(_('Page %s ever exists.'),$strNewName));	
		
		//reset the menucache
		if(!deleteMenuCache()) return false;
			
		//Copy the directory
		if( !parent::Copy($strCDirName, $strParentPath))
			return false;
		
		//then copy the ini file if exists
		if( !($oCDirCategory = &getFileObject($strCDirPath)) )
			return setError(_('Internal Error. In copy, page not exists'));
		
		if(is_file($this->oPConfigFile->path)){
			if( !$this->oPConfigFile->Copy($oCDirCategory->oPConfigFile->getName(),$strParentPath) )
				return false;
		}
		
		//reset the MENU NAME for the newfile, set the menu order and set the virtual name
		if(!$oCDirCategory->oPConfigFile->setParam('MENU_NAME',''))
			return false;
		if(!$oCDirCategory->oPConfigFile->setParam('MENU_ORDER','99'))
			return false;
		if( !$oCDirCategory->setVirtualName($strCPageName) )
			return false;

		return true;				
	}	
	/**
	 * Delete the category.
	 * First delete the config file if exists, then the menu cache, then the directory.
	 *
	 * @return true if succeed
	 */
	function Delete(){
		//delete the config file if exists
		if(is_file($this->oPConfigFile->path) && !$this->oPConfigFile->Delete())
				return false;
		//delete the menu cache
		if(!deleteMenuCache()) return false;
		return parent::Delete();
	}
		
	function findFile($fileName, $filter=false){
		$filter = ($filter===false)?$filter:$this->$ONLY_FILES;
		$tabFile = $this->listDir($filter,true,$fileName);
		if(sizeof($tabFile)==0) return false;
		return getFileObject($tabFile[0]);
	}
	
	function createFile($filename){
		$filename = trim($filename);
		if(!$this->checkName($filename))
			return false;
		//check that the page not exists
		if(getFileObjectAndFind($this->path.SLASH.$this->getUnixName($filename))){
			return setError(sprintf(_('The page %s ever exists'),$filename));
		}
		if( preg_match('/\.([a-z]*)$/i',$filename, $tabExt) ){
			if( !($tabExt[1] == 'html' || $tabExt[1] == 'htm' || $tabExt[1] == 'lnk') ){
				return setError(sprintf(_('%s file extension is not allowed'),$tabExt[1]));
			}else{
				if(preg_replace('/\.[a-z]*$/i','',$filename) == '' )
					return setError(_('You must specify a name for the file to create.'));
			}
		}
		$filenameori = $filename;

		if(strlen($filename)==0)
			return setError(_('You must specify a name for the file to create.'));

		if( !preg_match('/\.[a-z]*$/', $filename) )
			$filename .= '.html';
		
		$strPageName = preg_replace('/\.[a-z]*$/','',$filename);
		$strFileName = $this->getUnixName($filename);
		
		
		//On vérifie qu'un fichier du même nom n'existe pas dans le répertoire
		if($this->findFile($strFileName) !== false)
			return setError('La page '.$strPageName.' existe déjà');
			
		//on supprime le cache du menu
		if(!deleteMenuCache()) return false;
		
		if(!parent::createFile($strFileName)) return false;
		
		if( !($oPage = getFileObject($this->path.SLASH.basename($strFileName))) )
			return setError('internal error, pdircategory, createFile');
		
		if(!$oPage->setVirtualName($strPageName))
			return false;

		return $oPage;
	}

	function createDir($strDirName){
		
		$strDirName = trim($strDirName);
		if(!$this->checkName($strDirName))
			return false;
		
		$strPageName = $strDirName;
		$strDirName = $this->getUnixName($strPageName);
		
		if(getFileObjectAndFind($this->path.SLASH.$strDirName,'dir') !== false)
			return setError(sprintf(_('The directory %s exists.'),$strDirName));

		//on supprime le cache du menu
		if(!deleteMenuCache()) return false;
		
		if(!parent::createDir($strDirName)) return false;
		
		//set the virtual name
		if( !($oDir = getFileObject($this->path.SLASH.basename($strDirName))) )
			return setError('internal error, pdircategory, createDir');

		if(!$oDir->setVirtualName($strPageName))
			return false;

		return true;	
	}

	function createLink($strLinkName=false){
		if(!$strLinkName)
			$strLinkName = _('link ').$this->getMenuName().'.lnk';
		$oDirParent = getFileObject($this->getParentPath());
		if(!$oDirParent->createFile($strLinkName))
			return false;
		if( !($oLink = getFileObjectAndFind($oDirParent->path.SLASH.$oDirParent->getUnixName($strLinkName))) ){
			return setError('Internale Error. Can not find the created link file');
		}
		if(!$oLink->setLinkedPage($this))
			return false;
		return true;
	}
	

}//END CLASS
}//END DEFINE