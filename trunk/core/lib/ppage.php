<?php
if(!defined("PPAGE")){
define("PPAGE",0);
require(SITE_PATH.'core/lib/ptextfile.php');
require(SITE_PATH.'core/lib/peventsmanager.php');

/**
 * Class PPAGE.
 * This class is used to manipulate web site pages.
 *
 * A page is the association of two files. The html file and the ini file associated to this html file.
 * The html file must begin with a number 01_test.html. This number will be used to generate the order of the
 * file in the menu. The page options are stored in an ini file 01_test.ini, so the html file and the ini file has 
 * the same basename.
 * Example: 01_Home.html
 * 			02_Home.ini
 */

class PPage extends PTextFile {
	var $oPConfigFile;//the config file of the page
	var $oCatParent;//the object pdircatecory of the current page
	
	function PPage($strPath){
		parent::PTextFile($strPath);
		$this->oPConfigFile = new PConfigFile(dirname($this->path).SLASH.basename($this->getNameWithoutExt()).".ini",CONFIG_FILE);
		$this->oCatParent = new PDirCategory($this->getParentPath());
	}
	
	/**
	 * Return the unique id of the page.
	 * In fact the file url whitout extension as .html but replace '/' by string 'slash'
	 * @return string the id of the page, it is in fact the url without the /
	 */
	function getId(){
		return str_replace('.html','',str_replace('/','slash',preg_replace('/^'.preg_quote(SITE_URL,'/').'/','',$this->getUrl(true))));
	}
	
	function checkName($strName=false){
		if($strName === false) $strName = basename($this->path);
		
		if(preg_match('/\.php|\.cgi/i',$strName)) {
			return setError(_('This kind of file is not allowed.'));
		}
		return true;
	}
	
	function Display($thumb_size=100,$url=false,$oRootDir=false){
		if(!$url) $url=$this->getDisplayUrl();
		$strReturn='';
		$strReturn.= '<dl class="file" id="filename='.str_replace(SLASH,'/',$this->getRelativePath()).'">'."\n"; 
		$this->DisplayMenu(($oRootDir?$oRootDir->getRelativePath():''));
		
		$strSpanClass = (!$this->isShowInMenu())?' class="notvisible"':' class="pagevisible"';
		$strSpanClass = (!$this->isCached())?' class="nocache"':$strSpanClass;
		$strSpanClass = (!$this->isPublished())?' class="draft"':$strSpanClass;
		
		$strReturn.= "\t<dt>\n\t\t<a class=\"fileLink\" href=$url>";
		$strReturn.= "<img src='".$this->getMimeIconUrl($thumb_size)."'"; 
		$strReturn.= " id=\"context_menu_".$this->getIdName()."\" ";
 		$strReturn.= " />";
 		$strReturn.= "</a>\n\t</dt>\n";
		$strReturn.= "\t<dd><span $strSpanClass>".$this->getPrintedName()."</span></dd>\n";
		$strReturn.= "</dl>\n";
		return $strReturn;
	}
	
	function menuDelete(){
		return parent::menuDelete(_('the page'));
	}
	
	function getMenuSpecificsItems(){
		$strMenu = parent::getMenuSpecificsItems();

		//create a link
		$strLabel = _('Create Link');
		$strMenu .= "\n".'<li><a href="javascript:createLink(\''.urljsencode($this->getRelativePath()).'\');" id="createlink" >'.$strLabel.'</a></li>';
		
		//show hide in menu
		$bVisible = $this->isShowInMenu();
		$strLabel = $bVisible?_('Hide'):_('Show');
		$strVisible = $bVisible?'false':'true';
		$strMenu .= '<li><a href="javascript:setPageConfigVar(\''.urljsencode($this->getRelativePath()).'\',\'SHOW_IN_MENU\','.$strVisible.');" id="hideMenu" >'.$strLabel.'</a></li>';

		$bPublished = $this->isPublished();
		$strLabel = $bPublished?_('Unpublish'):_('Publish');
		$strPublished = $bPublished?'false':'true';
		$strMenu .= "\n".'<li><a href="javascript:setPageConfigVar(\''.urljsencode($this->getRelativePath()).'\',\'PUBLISHED\','.$strPublished.');" id="hideMenu" >'.$strLabel.'</a></li>';
		
		$bCached = $this->isCached();
		$strLabel = $bCached?_('No Cache'):_('Use Cache');
		$strCached = $bCached?'false':'true';
		$strMenu .= "\n".'<li><a href="javascript:setPageConfigVar(\''.urljsencode($this->getRelativePath()).'\',\'CACHING\','.$strCached.');" id="hideMenu" >'.$strLabel.'</a></li>';
		
		return $strMenu;	
	}
	
	
	/**
	 * function Save.
	 * If the $strTextConfig is set, save the config file with this text.
	 * Then launch the event manager for event savepage.
	 * Finally call the parrent::Save fonction to save the text in the html file.
	 *
	 * @param string the text of the html file
	 * @param string (optional, default false) the text of the config file
	 * @return boolean true if suceed, false if an error occured
	 */
	function Save($text, $strTextConfig=false){
		$text=stripslashes($text);
//		$text=str_replace('src="'.SITE_URL,'src="{#SITE_URL#}',$text);
		
		$strMediasUrl = POFile::getPathUrl(MEDIAS_PATH);		
		$text=str_replace('src="'.$strMediasUrl,'src="{$MEDIAS_URL}',$text);
		$text=str_replace('href="'.$strMediasUrl,'href="{$MEDIAS_URL}',$text);
		
		//save the config file, if content has been changed
		$strContentConfig = is_file($this->oPConfigFile->path)?file_get_contents($this->oPConfigFile->path):'';
		if($strTextConfig && $strTextConfig != $strContentConfig){
			if(!$this->oPConfigFile->Save($strTextConfig))
				return false;
		}
		
		//do not save the page, if content has not been changed
		$strContent = is_file($this->path)?file_get_contents($this->path):'';
		if( $text == $strContent )
			return true;
		
		//save the history if file exists
		if( is_file($this->path) ){
			// save history folder and file, only one backup per minute
			$iCTime = time();
			$iCTime=$iCTime-date('s',$iCTime);//calculate the timestamp for current minute whitout seconds
			$oDirHistoryCache = new PDir(CACHE_HIST_DIR.SLASH.$this->getId().SLASH.$iCTime);
			//create the directory if not exists
			if(!is_dir($oDirHistoryCache->path))
				if(!$oDirHistoryCache->mkdir())
					return false;
				
			/* html file */
			$oHTMLHistory = new PTextFile($oDirHistoryCache->path.SLASH.basename($this->path));
			if(!$oHTMLHistory->Save($strContent))
				return false;
			
			/* ini file */
			if( is_file($this->oPConfigFile->path) ){
				$oConfigHistory = new PConfigfile($oDirHistoryCache->path.SLASH.basename($this->oPConfigFile->path));
				if( !$oConfigHistory->Save($strContentConfig) )
					return false;
			}
		}

		//when saving must load the new file due to cache
		if(doEventAction('savepage',array(&$text,&$this)) === false)
			return false;

		return parent::Save($text);
	}
	
	/**
	 * Delete the current page.
	 * While delete the page, delete the html file, the history directory, and the ini file.
	 * An event is attached to this function: deletepage event.
	 * Use for example by the search engine plugin.
	 *
	 * @return true if succeed, else return false.
	 */
	function Delete(){
		// delete history folder and file
		$oDirHistoryCache = new PDir(CACHE_HIST_DIR.SLASH.$this->getId());
		if(!$oDirHistoryCache->Delete()) return false;

		//delete the config file;
		if(!$this->oPConfigFile->Delete()) return false;
		
		if(!doEventAction('deletepage',array(&$this)))
			return false;

		//on supprime le cache du menu
		if(!deleteMenuCache()) return false;
		
		//delete the html file
		return parent::Delete();
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
	
	function getModifiedTime(){
		$iTimeConfig = is_file($this->oPConfigFile->path)?filemtime($this->oPConfigFile->path):0;
		$iTimePage = is_file($this->path)?filemtime($this->path):0;
		return ($iTimePage>$iTimeConfig)?$iTimePage:$iTimeConfig;
	}
	
	/**
	 * Rename the page.
	 * This function is called to rename the page.
	 * If the new name of the page contains specific caracters as éà, it is replace by ea,
	 * but the menu name is set with special caracters
	 * An event is attached to this fonction: renamepage
	 * This event is used for example by the search engine plugin to index the file content.
	 * 
	 * The difference between move and rename, is that in move do not set the menu title !!
	 * @param string $newname, the new name (basename)
	 * @param string $destDir, if == false the parent dir
	 * @return true if suceed
	 */
	function Rename($newname, $destDir=false){
		$fileNewName = $this->getUnixName($newname);
		$pageNewName = $newname;
		$pageCurrName = $this->getVirtualName();
		$fileCurrName = $this->getName();

		if(strlen($fileNewName)==0) return setError(_('Can not rename with empty name'));
		
		//manage extensions
		$oFile = new PFile($fileNewName);
		if($oFile->getExtension()=='')
			$fileNewName .= '.'.$this->getExtension();
		else{
			$oFile = new PFile($pageNewName);
			$pageNewName = $oFile->getNameWithoutExt();
		}
		if($fileNewName == $fileCurrName && $pageCurrName == $pageNewName && $destDir === false) return true;
		
		
		//check the name is valid, not a php file or a cgi one for example
		if(!$this->checkname($pageNewName)) return false;

		//set the menu name in the ini file if has been renamed !! not moved
		if( !$destDir && !$this->setVirtualName($pageNewName) )
			return false;

		if( $fileCurrName != $fileNewName || $destDir !== false ){
			//if history dir exists, rename it
			$oDirHistoryCache = new PDir(CACHE_HIST_DIR.SLASH.$this->getId());
			if(is_dir($oDirHistoryCache->path)){
				$destDirModify = (!$destDir)?$this->getParentPath():$destDir;
				if(substr($destDirModify,-1)==SLASH)
					$destDirModify=substr($destDirModify,0,strlen($destDirModify)-1);
					
				$oPageNewHistory = new PPage($destDirModify.SLASH.$fileNewName);
				$strNewHistoryName = $oPageNewHistory->getId();
				if($this->getId() != $strNewHistoryName)
					if(!$oDirHistoryCache->Rename($strNewHistoryName))
						return false;
			}
			
			if(!doEventAction('renamepage',array(&$this,(($destDir)?$destDir:$this->getParentPath()).SLASH.$fileNewName)))
				return false;
				
			//on supprime le cache du menu
			if(!deleteMenuCache()) return false;
			
			//rename the config file if exists
			if(is_file($this->oPConfigFile->path)){
				$oFileTmp=new PFile($fileNewName);
				if(!$this->oPConfigFile->Rename($oFileTmp->getNameWithoutExt().".ini",$destDir))
					return false;
			}
			if(!parent::Rename($fileNewName, $destDir))
				return false;
		}
		return true;
	}
	
	/**
	 * Copy a page. Check the name of the new copy, then copy the html file and the inifile.
	 * Also set the virtual name, and reset the MENU NAME and the MENU_ORDER
	 *
	 * @param string $strNewName, the new name of the file. If the file extension is not set use the original one.
	 * @param string $strParentPath, path of the dire where will be but the copy. If not set use the parent path of the
	 * copied page.
	 * @return boolean true if succeed, else false.
	 */
	function Copy($strNewName,$strParentPath=false){
		$strCPageName = $strNewName;
		$strCFileName = $this->getUnixName($strNewName);
		
		if(strlen($strCFileName)==0) return setError(_("Can not copy page with empty name."));
		if( !$this->checkname($strCPageName) ) return false;
		if(!$strParentPath) $strParentPath=$this->getParentPath();
		
		$strCFileName .= ".".$this->getExtension();
		$strCFilePath = $strParentPath.SLASH.$strCFileName;
		
		if ( getFileObjectAndFind($strCFilePath) )
			return setError(sprintf(_('Page %s ever exists.'),$strNewName));	
			
		//Copy the html file
		if( !parent::Copy($strCFileName, $strParentPath))
			return false;
		
		//then copy the ini file if exists
		if( !($oCPage = &getFileObject($strCFilePath)) )
			return setError(_('Internal Error. In copy, page not exists'));
		
		if(is_file($this->oPConfigFile->path)){
			if( !$this->oPConfigFile->Copy($oCPage->oPConfigFile->getName(),$strParentPath) )
				return false;
		}
		
		//reset the MENU NAME for the newfile, set the menu order and set the virtual name
		if(!$oCPage->oPConfigFile->setParam('MENU_NAME',''))
			return false;
		if(!$oCPage->oPConfigFile->setParam('MENU_ORDER','99'))
			return false;
		if( !$oCPage->setVirtualName($strCPageName) )
			return false;

		return true;				
	}
	
	function getEditorFileContent(){
		if(!is_file($this->path))
			return '';
		$text =  file_get_contents($this->path);
		$text = preg_replace("/src=\"{#SITE_URL#}".preg_quote(SITE,'/')."/","src=\"".SITE_URL.SITE,$text);
		
		$text = str_replace('{$MEDIAS_URL}',POFile::getPathUrl(MEDIAS_PATH),$text);
		
		return $text;
	}
	
	
	function getHistoryList(){
		if(!is_file($this->path))
			return array();
			
		$oDirHistory = new PDir(CACHE_HIST_DIR.$this->getId());
		$tabReturn = array();
		//the current version, before editing
		$tabReturn[]=array('PATH'=>$this->path,'PRINTNAME'=>_('Last Version'));

		if ($oDirHistory->isDir()) {
			$tabList = array_reverse($oDirHistory->listDir($oDirHistory->ONLY_DIR));
			foreach($tabList as $strDirHistoryVersion){
				$pDirVersion = new PDir($strDirHistoryVersion);
				$listFiles = $pDirVersion->listDir($oDirHistory->ONLY_FILES,true, $filter='\.htm[l]?$');
				if(sizeof($listFiles)>0){
					$iFileTime = basename($pDirVersion->path);
					$strDay = (date('Y-m-d',time()) == date('Y-m-d',$iFileTime))?_('today at'):date(_('Y-m-d'),$iFileTime);
					$strPrintName = $strDay.' '.date(_('H:i'), $iFileTime);
					$tabReturn[]=array('PATH'=>$listFiles[0],'PRINTNAME'=>$strPrintName);
				}
				
			}
		}
		return $tabReturn;		
	}
	
	/**
	 * Return the url of the page
	 *
	 * @param boolean $bUseUrlRewriting, must to use the url rewriting, by default true
	 * @return string the url of the page.
	 */
	function getUrl($bUseUrlRewriting = true){
		$strPath = $this->getRelativePath();
		//return the path from the language path

		$strPath = preg_replace('/'.str_replace('/','\/',quotemeta(SITE.SLASH.PAGES.SLASH).'[a-zA-Z]{2,}'.quotemeta(SLASH)).'/','',$strPath);
		$strPath = strtolower($strPath);
		if($bUseUrlRewriting){
			$strPath = str_replace(' ','-',$strPath);
			$strPath = str_replace(rawurlencode(SLASH),'/',rawurlencode($strPath));
		}else{
			$strPath = rawurlencode($strPath);
		}
		return SITE_URL.(($bUseUrlRewriting)?'':'?page=').str_replace('index.html','',$strPath);
	}
	
	/**
	 *	Return an array with this elem strut (NAME=>,URL=>)
	 *
	 * @return 
	 */
	function getTabGuidage(){
		$tabReturn=array();
		$strPath = $this->path;

		while(PAGES_PATH != $strPath ){
			$o = &getFileObject($strPath);
			if(!preg_match('/index/',$o->getName())){
				$tabReturn[] = array('NAME'=>$o->getMenuName(),'URL'=>$o->getUrl());
			}
			$strPath=$o->getParentPath();
		}
		return array_reverse($tabReturn);
	}
	
	/**
	 * This is the page url but replace / by -
	 *
	 * @return string the page url separated by -
	 */
	function getPagePathForTitle(){
		$strReturn='';
		$tab=$this->getTabGuidage();
		$iNb=sizeof($tab);
		foreach($tab as $i=>$elem){
			$strReturn.=$elem["NAME"];
			if($i<($iNb-1)) $strReturn.=" - ";
		}
		return $strReturn;
	}
		
	/**
     * For editable file return page in the web site	
	 */
	function getDisplayUrl(){
		if(eregi(TEXTEDIT_WYSWYG,$this->getname()))			
			return "admin_file_editor.php?file=".urlencode($this->getRelativePath());
		return '"'.$this->path.'" target="_blank"';
	}
	
	/**
	 * This function is called by the file browser for displaying the page name.
	 * Now use the menu name (with special caracters).
	 * 
	 * @return string the name to display in the file browser
	 */
	function getPrintedName(){
		return $this->getVirtualName();
	}
	
	
	function getMenuName(){
		if(($strConfigName=$this->getConfig("MENU_TITLE"))!="") 
			$menuName = $strConfigName;
		else 
			$menuName = $this->getVirtualName();		
		return $menuName;
	}
	
	
	function getVirtualName(){
		if(($strConfigName=$this->getConfig("VIRTUAL_NAME"))!="") 
			return $strConfigName;
		else 
			return $this->getNameWithoutExt();		
	}
	
	function setVirtualName($strNewName){
				
		if( !$this->oPConfigFile->setParam('VIRTUAL_NAME',addslashes($strNewName)) )
			return false;
		
		if(!$this->oPConfigFile->Save())
			return false;
			
		return true;
	}
	
	function getMenuOrder(){
		if(($strConfigName=$this->getConfig("MENU_ORDER"))!="") 
			return intVal($strConfigName);
		return 99;
	}
	
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
	
	function getConfigFileObject(){
		return $this->oPConfigFile;
	}
	function getConfigFileObjectCategory(){
		return $this->oCatParent->oPConfigFile;
	}
	
	function isCached(){
		return ($this->getConfig("CACHING") === "false")?false:true;
	}
	function isShowInMenu(){
		return ($this->getConfig("SHOW_IN_MENU") === "false")?false:true;
	}
	
	function isPublished(){
		return ($this->getConfig("PUBLISHED") === "false")?false:true;
	}
	
	/**
	 * This is an alias to this PconfigFile getDirectParam fonction.
	 *
	 * @param string strParam, the parameter to get
	 * @return the parameter if found, else return false.
	 */
	function getConfig($strParam){
		return $this->oPConfigFile->getDirectParam($strParam);
	}
	
	/**
	 * Return the template name of the page object.
	 * First read in the ini file the TEMPLATE parameter, if not set or set to AS_PARENT
	 * return the TEMPLATE name of the parent directory.
	 *
	 * @return string the template name of the page
	 */
	function getTemplateName(){
		$param = $this->getConfig("TEMPLATE");
		if( $param == 'AS_PARENT' || !$param || empty($param) )
			return $this->oCatParent->getTemplateName();
		return $param;
	}


	/**
	 * 
	 *
	 * @return unknown
	 */
	
	function DisplayEditor(){
		$strReturn = '';
		
		$strFCKConfigDir = SITE.SLASH.'config'.SLASH.'fckeditor'.SLASH;
		$strFCKConfigUrl = SITE_URL.((SLASH=='/')?$strFCKConfigDir:str_replace(SLASH,'/',$strFCKConfigDir)); 
		$strFCKConfigDir = CONFIG_DIR.'fckeditor'.SLASH;
		
		$oFCKeditor = new FCKeditor('text') ;
		$oFCKeditor->BasePath = SITE_URL.'vendors/jscripts/fckeditor/';
		$oFCKeditor->Value = $this->getEditorFileContent();
		$oFCKeditor->Height = '400' ;
		
		//the styles to apply to the editor, the page style and the fckeditor.css style
		$fileStyleUrls='';
		if($pageStyle = $this->getConfig("PAGE_STYLE")) $fileStyleUrls .= THEME_URL.'css/'.$pageStyle.',';
		if(is_file($strFCKConfigDir.'fckeditor.css')) $fileStyleUrls .= $strFCKConfigUrl.'fckeditor.css';
		$oFCKeditor->Config["EditorAreaCSS"] =$fileStyleUrls;
		
		$oFCKeditor->Config["ImageBrowserURL"] = SITE_URL."core/admin/admin_file_selector.php?rootpath=".urlencode(POFile::getPathRelativePath(MEDIAS_PATH));
		$oFCKeditor->Config["FlashBrowserURL"] = SITE_URL."core/admin/admin_file_selector.php?rootpath=".urlencode(POFile::getPathRelativePath(MEDIAS_PATH));
		//go to the parent path
		$oFCKeditor->Config["LinkBrowserURL"] = SITE_URL."core/admin/admin_file_selector.php?current_dir=".urlencode(POFile::getPathRelativePath($this->getParentPath(), PAGES_PATH))."&rootpath=".urlencode(POFile::getPathRelativePath(PAGES_PATH));
		$oFCKeditor->Config["CustomConfigurationsPath"] = SITE_URL.'index.php?page=fckconfig.js';
		
		if(is_file($strFCKConfigDir.'fcktemplates.xml'))
			$oFCKeditor->Config["TemplatesXmlPath"]	= SITE_URL.'index.php?page=fcktemplates.xml' ;
		if(is_file($strFCKConfigDir.'fckstyles.xml'))
			$oFCKeditor->Config["StylesXmlPath"]	= SITE_URL.'index.php?page=fckstyles.xml' ;
		
		
		$strBarName = getUserEditorBar();
		if($strBarName) $oFCKeditor->ToolbarSet = $strBarName;
		
	
		$strTabsTpl ='
		<div id="tabPageEditor">
			<ul>
				<li class="ui-tabs-nav-item"><a href="#fragEditor"><span>Edition</span></a></li>
				<li class="ui-tabs-nav-item"><a href="#fragOptions"><span>Options</span></a></li>
			</ul>
			
			<div id="fragEditor">{TAB_EDITOR}</div>
			<div id="fragOptions">{TAB_OPTIONS}</div>
		</div>';
		
		$strFragEditorContent = '
			<form action="'.$_SERVER["REQUEST_URI"].'" method="POST" id="form_editor" onSubmit="return actionClickOnSaveHtml(this,\''.$this->getUrl().'\',\''.urljsencode($_GET['file']).'\');" style="text-align:right">
				<div class="fckEditor">
					'.$oFCKeditor->CreateHtml().'
				<div class="panelHistory">
					<h4>'._('History').'</h4>
					{HISTORY_CONTENT}
				</div><!-- end history -->
				</div><!-- end fckblock -->
				<!-- buttons -->
				<div style="text-align:right;clear:both;">
					<button class="ui-state-default ui-corner-all" type="button" onClick="MyCancel();">'._('cancel').'</button>
					<button class="ui-state-default ui-corner-all" type="button" onClick="$(\'.openTabs:first\').trigger(\'click\');">'._('history').'</button>
					<button class="ui-state-default ui-corner-all" type="submit" onClick="this.form.elements[\'view\'].value=true;">'._('save & see').'</button>
					<button class="ui-state-default ui-corner-all" type="submit">'._('save').'</button>
				</div>
				<input type="hidden" value="false" name="view" />
				<input type="hidden" name="todo" value="save" />
			</form>
		';
		 // GESTION DE L'HISTORIQUE
		 
		$strHistoryContent='<ul class="historylist">{LIST_HISTORY}</ul>';
		$strHtmlListHistory='';
		$tabHistory = &$this->getHistoryList();
		if(sizeof($tabHistory) == 0)
			$strHistoryContent = _('History empty !');
		else {
			$i=0;
			foreach($tabHistory as $elemHistory){
				$strHtmlListHistory.='<li><a class="itemHistory '.(($i==0)?'selected':'').'" href="#" onclick="loadHistoryPage(\''.$elemHistory['PATH'].'\',this);return false;">'.$elemHistory['PRINTNAME'].'</a></li>';
				$i++;
			}				
			$strHistoryContent = str_replace('{LIST_HISTORY}',$strHtmlListHistory,$strHistoryContent);
		}
		$strFragEditorContent = str_replace('{HISTORY_CONTENT}',$strHistoryContent,$strFragEditorContent);
	
		$strFragOptionsContent = $this->oPConfigFile->DisplayEditor();
		
		$strReturn = str_replace(array('{TAB_EDITOR}','{TAB_OPTIONS}'),array($strFragEditorContent,$strFragOptionsContent),$strTabsTpl);
		return $strReturn;
	}
	
	
}//END CLASS
}//END DEFINE

?>