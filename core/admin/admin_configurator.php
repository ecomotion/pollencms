<?php include 'admin_top.php';?>
<?php
$tabMainTabs = array();

$oTextConfigFile = new PTextFile($configFile->path);
$tabMainTabs[]=	array(
	'FRAG_NAME'=>'frag_config',
	'TAB_NAME'=>_('Site Configuration'),
	'TAB_CONTENT'=>array_to_tabs(array(
		array(
			'FRAG_NAME'=>'site_config',
			'TAB_NAME'=>_('Site Parameters'),
			'TAB_CONTENT'=>$configFile->DisplayEditor('actionClickOnSaveSiteConfig')
		),
		array(
			'FRAG_NAME'=>'hotkeys_management',
			'TAB_NAME'=>_('Manage Hot Keys'),
			'TAB_CONTENT'=>$configFile->DisplayEditor('actionClickOnSaveConfig','HOTKEYS')
		),
		/*array(
			'FRAG_NAME'=>'users_management',
			'TAB_NAME'=>_('Manage Users Accounts'),
			'TAB_CONTENT'=>'<h2>Users Accounts</h2>'.$configFile->getHtmlUsersList()
		),*/
		array(
			'FRAG_NAME'=>'site_cache',
			'TAB_NAME'=>_('Cache Management'),
			'TAB_CONTENT'=>'
					<div style="padding-top:60px">
						<button onClick="clickOnClearCache(this,\''._('Clearing cache ....').'\',\'site\');" class="ui-state-default ui-corner-all" type="button">'._('clear site cache').'</button>
						<button onClick="clickOnClearCache(this,\''._('Clearing cache ....').'\',\'thumbs\');" class="ui-state-default ui-corner-all" type="button">'._('clear thumbs cache').'</button>
						<button onClick="clickOnClearCache(this,\''._('Clearing cache ....').'\',\'history\');" class="ui-state-default ui-corner-all" type="button">'._('clear history cache').'</button>
					</div>
				'
		),
		array(
			'FRAG_NAME'=>'site_file_config',
			'TAB_NAME'=>_('Config File'),
			'TAB_CONTENT'=>$oTextConfigFile->DisplayEditor()
		)
	),'tabConfiguratorLevel2')
);

/**Models **/
$strModelContent ='';
$strUrltabModels = 'admin_file_management.php?current_dir='.urlencode(POFile::getPathRelativePath(PAGES_MODELS_PATH));
if(!is_dir(PAGES_MODELS_PATH)){
	$oDirModels = new PDir(PAGES_MODELS_PATH);
	if(!$oDirModels->mkdir())
		$strModelContent .= getError();
}
if(is_dir(PAGES_MODELS_PATH)){
	$oDirModels = &getFileObject(PAGES_MODELS_PATH);
	$tabListModels = $oDirModels->listDir($oDirModels->ONLY_FILES);
	foreach($tabListModels as $filePath){
		if(is_file($filePath)){
			$oTemp = &getFileObject($filePath);
			//$strModelContent.=get_class($oTemp);
			$strModelContent .= $oTemp->Display(70,$url=false,$oDirModels);
		}
	}
}

$tabMainTabs[]=	array(
			'FRAG_NAME'=>'models_link',
			'TAB_NAME'=>_('Models Management'),
			'TAB_CONTENT'=>'
			<form>
				<button onClick="myRelodPage(\''.$strUrltabModels.'\')" class="ui-state-default ui-corner-all" type="button">'._('Manage').'</button>
			</form>
			<div id="listTypes">'.$strModelContent.'</div>'
		);

/**
	TAB FCKEDITOR
*/
$oFile_fck_toolbar = new PTextFile(CONFIG_DIR.'fckeditor'.SLASH.'fckconfig.js');
$oFile_fck_styles = new PTextFile(CONFIG_DIR.'fckeditor'.SLASH.'fckstyles.xml');
$oFile_fck_css = new PTextFile(CONFIG_DIR.'fckeditor'.SLASH.'fckeditor.css');
$oFile_fck_templates = new PTextFile(CONFIG_DIR.'fckeditor'.SLASH.'fcktemplates.xml');

$tabMainTabs[] = array(
	'FRAG_NAME'=>'conf_editor',
	'TAB_NAME'=>_('FCK Editor'),
	'TAB_CONTENT'=>array_to_tabs(array(
		array(
			'FRAG_NAME'=>'fck_toolbar',
			'TAB_NAME'=>'FCK TOOLBAR',
			'TAB_CONTENT'=>$oFile_fck_toolbar->DisplayEditor()
			),
		array(
			'FRAG_NAME'=>'fck_styles',
			'TAB_NAME'=>'FCK Styles',
			'TAB_CONTENT'=>$oFile_fck_styles->DisplayEditor()
			),
		array(
			'FRAG_NAME'=>'fck_editor_css',
			'TAB_NAME'=>'FCK Editor CSS',
			'TAB_CONTENT'=>$oFile_fck_css->DisplayEditor()
			),
		array(
			'FRAG_NAME'=>'fck_templates',
			'TAB_NAME'=>'FCK Templates',
			'TAB_CONTENT'=>$oFile_fck_templates->DisplayEditor()
		)
	),'tabConfiguratorLevel2')
);

/**
	TAB PLUGINS
*/
$strTableActivated='<h2>'._('Activated Plugins list').'</h2>
<table id="listPluginsActivated" class="listPlugins">
	<tr>
		<th>'._('Name').'</th>
		<th>'._('Author').'</th>
		<th>'._('Version').'</th>
		<th>'._('Action').'</th>
	</tr>
	{LIST}
</table>
';
$strTableAvailable='<h2>'._('Available Plugins list').'</h2>
	<table id="listPluginsAvailables"  class="listPlugins">
		<tr>
			<th>'._('Name').'</th>
			<th>'._('Author').'</th>
			<th>'._('Version').'</th>
			<th>'._('Action').'</th>
		</tr>
		{LIST}
	</table>
';
$strListActivated='';
$strListAvailable='';
if(is_dir(PLUGINS_DIR)){
	$oPdir = getFileObject(PLUGINS_DIR);
	
	//parse each plugin directory
	$listDir = $oPdir->listDir($oPdir->ONLY_DIR);
	foreach($listDir as $strPluginPath){
		$oDirPlugin = new PPluginDir($strPluginPath);
		$bActivated = false;
		$configFile->getParam($oDirPlugin->getIdName(),$bActivated,'PLUGINS');
		$strAction = ($bActivated === "true")?_('UnActivate'):_('Activate');
		$strLine = '<tr>
			<td>'.$oDirPlugin->getPluginName().'</td>
			<td>'.$oDirPlugin->getAuthor().'</td>
			<td>'.$oDirPlugin->getVersion().'</td>
			<td><a href="#" onClick="return toggleactivatePlugin(\''.urljsencode($oDirPlugin->getName()).'\',\'false\');">[ '.$strAction.' ]</a></td>
		</tr>';
		if($bActivated === "true"){
			$strListActivated .= $strLine;
		}else {
			$strListAvailable .= $strLine;
		}		
	}
}

$strContent = '<div id="listPlugins">
'.str_replace('{LIST}',$strListActivated,$strTableActivated).'
'.str_replace('{LIST}',$strListAvailable,$strTableAvailable).'
</div>';
$tabPlugins = array();
$tabPlugins[]=array('FRAG_NAME'=>'plugins_list','TAB_NAME'=>_('Plugins List'),'TAB_CONTENT'=>$strContent);

$tabExtraPlugins = array();
if(!doEventAction('addPluginsTabs',array(&$tabExtraPlugins)))
	printError();
else{
	$tabPlugins = array_merge($tabPlugins,$tabExtraPlugins);
}

$tabMainTabs[] = array(
	'FRAG_NAME'=>'plugins',
	'TAB_NAME'=>_('Plugins'),
	'TAB_CONTENT'=>array_to_tabs($tabPlugins,'tabConfiguratorLevel2')
);

/**
	TAB ABOUT
*/
$tabMainTabs[] = array(
	'FRAG_NAME'=>'about',
	'TAB_NAME'=>_('About'),
	'TAB_CONTENT'=>'<div><strong>Pollen CMS version :</strong> '.POLLEN_CMS_VERSION.'</div>'.
	'<div class="blockLicence"><strong>Licence:</strong><br /><textarea readonly>'.
	file_get_contents(SITE_PATH."Licence.txt").
	'</textarea></div>'
);

echo '<div style="height:20px"></div>'.array_to_tabs($tabMainTabs,'tabConfigurator');

function array_to_tabs($aArray,$strWrapperClass){
	$strTpl = '
		<ul>
			{LISTE_ONGLETS}
		</ul>
		{ONGLETS_CONTENT}
	';
	
	$strListeOnglets=$strOngletsContent='';
	foreach($aArray as $aTab){
		$strListeOnglets .= '<li class="ui-tabs-nav-item"><a href="#'.$aTab['FRAG_NAME'].'"><span>'.$aTab['TAB_NAME'].'</span></a></li>'."\n";
		$strOngletsContent .= '<div id="'.$aTab['FRAG_NAME'].'">
		'.$aTab['TAB_CONTENT'].'
		</div>';
	}
	return '<div class="'.$strWrapperClass.'">'.
		str_replace(array('{LISTE_ONGLETS}', '{ONGLETS_CONTENT}'),array($strListeOnglets, $strOngletsContent),$strTpl).'
		</div>
		';
}


include 'admin_bottom.php';
?>

