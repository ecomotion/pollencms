<?php
addPollenPlugin('deletepage','se_on_delete_page');
addPollenPlugin('savepage','se_on_save_page');
addPollenPlugin('renamepage','se_on_rename_page');

addPollenPlugin('addPluginsTabs','seConfig');
addPollenPlugin('adminHeader','seAdminHeader');

function seChangeLocale(){
	setLocalePath(dirname(__FILE__).SLASH.'locale','search_engine');

	_('ACTIVATE');
	_('HOST');
	_('LOGIN');
	_('PASSWD');
	_('BASE');
	_('PREFIX');

}

function seAdminHeader(){
	$oDir = new PDir(dirname(__FILE__));
	$strCurrUrl = $oDir->getUrl();
	$strReturn = '
	<!-- Add By Plugin seAdminHeader -->
		<script language="JavaScript" src="'.$strCurrUrl.'include/js/apply-se.js" ></script>
	<!-- // -->
	';
	return $strReturn;
}

function seConfig($tabExtraPlugins){
	$oPlugin = new PPluginDir(dirname(__FILE__));
	$oConfigFile = &$oPlugin->oConfig;

	//change the local path for search engine translation
	seChangeLocale();
	
		$strUrl=$oPlugin->getUrl().'include/ajax_action.php';
	$strContent = '<h2>Actions</h2>
	<div><a class="pcmButton" href="javascript:seInitBase(\''.$strUrl.'\');">'._('Populate Data Base').'</a></div>
	<h2>Configuration</h2>
	'.$oConfigFile->DisplayEditor();
	$tabExtraPlugins[]=array(
		'FRAG_NAME'=>'plugins_searchengine',
		'TAB_NAME'=>_('Search Engine'),
		'TAB_CONTENT'=>$strContent
	);

	return true;
}


function se_on_delete_page(&$oPage) {
	seChangeLocale();
	require_once(dirname(__FILE__).SLASH.'include'.SLASH.'lib.searchengine.php');
	
	if(!se_get_config('ACTIVATE', $bActivate))
		return true;

	if(!$bActivate)
		return true;	

	return delete_info_search($oPage);
		
}

function se_on_rename_page(&$oPage, $strNewFileName) {
	//change the local path for search engine translation
	seChangeLocale();

	$oCurrentFile = new PFile(__FILE__);
	require_once($oCurrentFile->getParentPath().SLASH.'include'.SLASH.'lib.searchengine.php');

	if(!se_get_config('ACTIVATE', $bActivate))
		return $strSourceText;

	if(!$bActivate)
		return true;	

	return rename_info_search($oPage, $strNewFileName);
	
}

function se_on_save_page($strSourceText, &$oPage)
{
	seChangeLocale();
	//the lib to include
	$oCurrentFile = new PFile(__FILE__);
	require_once($oCurrentFile->getParentPath().SLASH.'include'.SLASH.'lib.searchengine.php');
	
	if(!se_get_config('ACTIVATE', $bActivate))
		return $strSourceText;		
	
	if(!$bActivate)
		return $strSourceText;	
	
	$returnval = true;
	if(!connectBdd())
		return false;

	if (exist_page_search($oPage->getUrl())) { $type = 'update'; } else { $type = 'insert'; }

	switch($type){
		case 'insert':
			$returnval = insert_info_search($oPage, $strSourceText);
			break;
		case 'update':
			$returnval = update_info_search($oPage, $strSourceText);
			break;
	}
	closeBdd();
	return ($returnval)?$strSourceText:false;
	
}


?>