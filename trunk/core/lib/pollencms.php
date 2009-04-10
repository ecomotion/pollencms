<?php

if(!defined("POLLENCMS")){
define("POLLENCMS",1);

require SITE_PATH.'core/config.inc.php';
include ("checkkey.php");
require (SITE_PATH.'core/lib/lib_functions.php');
require (SITE_PATH.'core/lib/peventsmanager.php');
require_once (SITE_PATH.SMARTY_DIR.'Smarty.class.php');
require (SITE_PATH.'core/lib/localization.php');
require (SITE_PATH.'core/lib/pdir.php');

class PPollenCMS extends Smarty {

	var $oPageCurrent;
	var $oEventsManager;
	
	/**
	 * Constructeur,
	 * initialise le moteur smarty, définie le répertoire du cache, des plugins ....
	 */
	function PPollenCms(){
		$this->cache_dir=SMARTY_CACHE_DIR."cache";
		$this->compile_dir=SMARTY_CACHE_DIR."compiled";
		$this->compile_id = (SITENAME)?SITENAME:'default';
		$this->template_dir=THEME_DIR.'templates';
		$this->assign('THEME_URL',THEME_URL);
		$this->assign('CONFIG_FILE_SITE',CONFIG_FILE);
		$configDescr = SITE_PATH.'core'.SLASH.'default_config.ini';
		if(is_file($configDescr))
			$this->assign('DEFAULT_CONFIG_FILE_SITE',$configDescr);
		
		//try to create the cache dir and compile dir if not exists!
		if(!is_dir($this->cache_dir)){
			$pdirCache = new PDir($this->cache_dir);
			if(!$pdirCache->mkdir()){
				printFatalError(sprintf(_('can not create the cache dir %s. Please check permissions.'),$pdirCache->getRelativePath()));
			}
		}
		
		if(!is_dir($this->compile_dir)){
			$pdirComile = new PDir($this->compile_dir);
			$pdirComile->mkdir();
		}
		
		$this->caching=1;
		$this->cache_lifetime=-1;
		$this->force_compile = isConnected();
						
		$this->plugins_dir[] = SITE_PATH.'core'.SLASH.'lib'.SLASH.'smartyplugins'.SLASH;
		
		//filters defined in plugins
		$this->loadDefaultFilters();
		
	}
		
	//only use for extra pages as sitemap.xml ....	
	function loadDefaultFilters(){
		$this->load_filter('pre', 'addconf');
		$this->load_filter('output','trimwhitespace');		
		$this->load_filter('output','copyleft');		
		$this->loadExtraFilters();
	}
	
	function loadAdminFilters(){
		$this->load_filter('output', 'admininclude');
	}
	
	function loadExtraFilters(){
		//load extraplugins
		global $oGlobalEventsManager;
		$tabFilters = getSmartyExtraFilters();
		foreach($tabFilters as $strType => $tabFilters){
			foreach($tabFilters as $filter) {
				if(isset($filter['FILEPATH'])){
					$this->plugins_dir[]=dirname($filter['FILEPATH']);
					if($filter['EVENT'] != 'insert' && $filter['EVENT'] != 'function' )
						$this->load_filter($strType,$filter['NAME']);					
				}else if(isset($filter['FUNCTION'])){
					switch($filter['TYPE']){
						case 'function':
							$this->register_function($filter['TPL_FCT_NAME'],$filter['FUNCTION']);
						break;
						case 'output':
							$this->register_outputfilter($filter['FUNCTION']);
						break;
						case 'pre':
							$this->register_prefilter($filter['FUNCTION']);
						break;
						case 'post':
							$this->register_postfilter($filter['FUNCTION']);
						break;
						case 'insert':
							$this->register_function($filter['TPL_FCT_NAME'],$filter['FUNCTION'],false);
						break;
					}
				}
			}
		}
	}
	
	function unloadFilters(){
		$this->unregister_outputfilter('admininclude');	
	}
	
	function getCompiledId(){
		return serialize($_GET);
	}
	
	/**
	 * Function: display
	 * If page has not been cached, calculate it.
	 * Then call the smarty display fonction
	 *
	 *
	 * param: &$oPage, a reference to the object page to display
	 */
	function displayPage(&$oPage){

		$strTemplateName=$oPage->getTemplateName();
		
		$strCompiledId= $this->getCompiledId();

		$this->oPageCurrent=&$oPage;
		$this->register_object('oPageCurrent',$oPage);
		
		//if page must not be cached, set the site cache to 0
		$this->caching=($this->caching && $oPage->isCached())?1:0;
		
		//on regénére le menu si le menu n'existe pas en cache ou si la page courante a été modifiée après le menu
		$bGenerateMenu = !is_file(SMARTY_CACHE_DIR.'menu.cache'); 
		//|| (is_file(SMARTY_CACHE_DIR.'menu.cache') && $oPage->getModifiedTime()>filemtime(SMARTY_CACHE_DIR.'menu.cache'));
		
		//rebuild menu not exist (a page has been updated) or the page if cache not exists or page not use cache
		if( $bGenerateMenu || $this->caching==0 ||  $this->force_compile /*if admin mode in fact */ || !$this->is_cached($strTemplateName,$strCompiledId) ){
			
			//check that the template file exists
			if(!is_file($this->template_dir.SLASH.$strTemplateName)){
				setError(sprintf(_("The template file %s doesn't exist."),$strTemplateName));
				printFatalHtmlError();
			}
			//si on doit regénérer le menu, on le supprime avant
			if($bGenerateMenu)
				if(!deleteMenuCache()) printFatalError();

			//assign the menu
			$this->assign_by_ref("menu",$this->getMenu(isConnected()));
			
			$this->assign("PAGE_PATH",$oPage->path);
			$this->assign("PAGE_CONTENU",$oPage->path);
			$this->assign("PAGE_PATH_TITLE",$oPage->getPagePathForTitle());
			
			$this->assign('MEDIAS_URL',POFile::getPathUrl(MEDIAS_PATH));
			
			$oPageConfigFile = $oPage->getConfigFileObject();
			if(is_file($oPageConfigFile->path)){
				$this->assign("CONFIG_FILE_PAGE",$oPageConfigFile->path);
			}
			$oCatConfigFile = $oPage->getConfigFileObjectCategory();
			if(is_file($oCatConfigFile->path)){
				$this->assign("CONFIG_FILE_CATAGORY",$oCatConfigFile->path);
			}
						
			//we load filter only in case of cache
			$this->loadAdminFilters();
			
		}//end calcul
		
		if(true || checkKey()){
			//display the page !!
			$strContent = parent::fetch($strTemplateName,$strCompiledId);
			doEventAction('beforedisplay',array(&$strContent, &$this));
			echo $strContent;
		}
		else{
			printError();
		}
	}
		

	/**
	 * Function: getMenu
	 * Generate a table of the structure of the site
	 * If adminMode is true take also the not published pages, else take only published page
	 * The menu is cached in the menu.cache file as a serialize element, in adminmode the cache is not use,
	 * but in production mode only the directory structure is parsed only if menu.cache file not exists. 
	 *
	 * @rev: 1.0 July 2008
	 * 
	 * @param adminMode, boolean take or not take the not published pages
	 * @return a table of the structure of the web site, each element of the menu contains:
	 * NAME of the element, the URL, the ID and the PATH to this element
	 *
	 */
	function getMenu($adminMode=false){
		$oMenuFile = new PTextFile(SMARTY_CACHE_DIR.'menu.cache');
			
		//if tabmenu is in cache and not in admin  mod, load the menu from cache
		if($adminMode === false && is_file($oMenuFile->path)){
			if($strMenu = unserialize(file_get_contents($oMenuFile->path)))
				return $strMenu;
		}
		
		$tabMenu = &$this->getMenuFromDir(getFileObject(PAGES_PATH), $adminMode);
		if($adminMode === false){
			if(!$oMenuFile->Save(serialize($tabMenu),false))
				die(getError());
		}
		return $tabMenu;
	}

	
	function getMenuFromDir($oCurrDir, $bAdminMode){
		$tabReturn = array();
		$tabPages = $oCurrDir->listDir($oCurrDir->ALL,$fullpath=true);
		foreach($tabPages as $strPagePath){
			//bug d'un fichier autre qu'une image
			if( !(is_file($strPagePath) && !preg_match('/\.htm[l]?$|\.lnk$/',$strPagePath)) ){
				$oPage = getFileObject($strPagePath);
				$strMenuItem=$oPage->getName();
				$url = $oPage->getUrl();
				$id = $oPage->getId();
				
				if( !is_dir($oPage->path) && $oPage->isShowInMenu()){
					if( $bAdminMode || $oPage->isPublished() ) {
						if(!preg_match('/index\./i',$oPage->getName()))
							$tabReturn[]=array('URL'=>$url, 'NAME'=>$oPage->getMenuName(), 'ID'=>$id, 'PATH'=>$oPage->path);
					}
				}else if(is_dir($oPage->path) && $oPage->isShowInMenu() ){ //is directory
					if( $oPage->isPublished() || $bAdminMode ){
						$tabSubMenu = $this->getMenuFromDir($oPage, $bAdminMode);
						$oPageIndex = $oPage->findFile('index');
						if($oPageIndex){
							if( !$oPageIndex->isShowInMenu() || !($oPageIndex->isPublished() || $bAdminMode) ){
								$oPageIndex=false;
							}
						}
						//the url of a directory is the first page, if the first page is not index
						if(sizeof($tabSubMenu) > 0 ){
							if(!$oPageIndex)
								$url=$tabSubMenu[0]['URL'];
							$tabReturn[]=array("URL"=>$url, "NAME"=>$oPage->getMenuName(), 'SUBMENU'=>$tabSubMenu, "ID"=>$id,'PATH'=>$oPage->path);
						}else if($oPageIndex){//if no page found test if index exist
							$tabReturn[]=array("URL"=>$url, "NAME"=>$oPage->getMenuName(), 'SUBMENU'=>$tabSubMenu, "ID"=>$id,'PATH'=>$oPage->path);					
						}
					}
				}
			}
		}
		return $tabReturn;
	}

	/**
	 * Function: getPage
	 *
	 * @param strPageUrl, this is the url of the page to found
	 */
	function getPage($strPageUrl, $adminMode=false){
		//first look in the menu cache
		$found = &$this->findPageFromMenuCache($this->getMenu($adminMode), SITE_URL.$strPageUrl,$adminMode);
		if($found && ($found->isPublished()||$adminMode==true)) return $found;

		if(substr($strPageUrl,-1)=="/")
			$strPageUrl=substr($strPageUrl,0,strlen($strPageUrl)-1);

		$found = &$this->getPageInDir(str_replace('/',SLASH,$strPageUrl), $adminMode, PAGES_PATH);
		if($found && ($found->isPublished()||$adminMode==true)) return $found;
		return false;
	}
	
	function findPageFromMenuCache(&$tabMenu,$strUrlSearched,$adminMode){
		foreach($tabMenu as $elemMenu){
			if($elemMenu['URL'] == $strUrlSearched && isset($elemMenu['PATH'])){
				$strPathFound = $elemMenu['PATH'];
				if(is_dir($strPathFound))
					return $this->getFirstPage($strPathFound,$adminMode);
				if(is_file($strPathFound) ){
				 	$oFile = &getFileObject($strPathFound);
				 	if( !$oFile->is_link() ) return $oFile;
				}
			}
			if(isset($elemMenu['SUBMENU'])){
				//look in the submenu
				$found = $this->findPageFromMenuCache($elemMenu['SUBMENU'],$strUrlSearched,$adminMode);
				if($found) return $found;
			}
		}
		return false;
	}
	
	function getPageInDir($page, $adminMode=false, $dir_parent){
		$abspage=$dir_parent.SLASH.$page;
		if(is_dir($abspage)){
			return $this->getFirstPage($abspage, $adminMode);	
		}
		return getFileObjectAndFind($abspage);
	}


	/**
	 * Function: getFirstPage
	 * return the first page of the current web site
	 *
	 *
	 * param: $dirSearch, the directory where to search, if not set, search in the PAGES_PATH
	 * return: false if not found, the first page
	 */
	function getFirstPage($dirSearch=false, $adminMode=false){
		$dirSearch = ($dirSearch==false)?PAGES_PATH:$dirSearch;

		if(!is_dir($dirSearch))
			return false;

		$oDirParent = &getFileObjectAndFind($dirSearch,'dir');
		if(!$oDirParent)
			return false;
		if( !($adminMode || $oDirParent->isPublished()) )
			return false;

		//Try to find the index file, if found and is publised, return
		$file=&getFileObjectAndFind($dirSearch.SLASH."index.html");
		if($file !== false && ($adminMode  || $file->isPublished()) ){
			return $file;
		}else {
			$file=&getFileObjectAndFind($dirSearch.SLASH."index.php");
			if($file !== false && ($adminMode  || $file->isPublished()) ){
					return $file;
			}
		}
		
		$tab = $oDirParent->listDir($oDirParent->ALL,$fullpath=true);
		if(sizeof($tab) == 0) return false;
		foreach($tab as $file){
			//bug d'une image placée à l'index du site
			if( !(is_file($file) && !preg_match('/\.htm[l]?$/',$file)) ){
				$oFile = &getFileObject($file);
				if( $adminMode !== false || $oFile->isPublished() ){
					if(is_file($oFile->path))
						return $oFile;
					else{
						$oFind = &$this->getFirstPage($oFile->path, $adminMode);
						if($oFind && $oFind->isPublished())
							return $oFind; 
					}				
				}
			}
		}
		return setError(__('No page found in the directory').': '.$dirSearch) ;
	}

}//end class

}/*end define*/
?>