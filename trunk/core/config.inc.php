<?php
/**
 * DO NOT EDIT THIS FILE, UPDATE CONF PARAMETET IN THE CONFIG.INI.PHP OF THE WEBSITE
 */
if(!defined("CONFIG")){
	define("CONFIG",1);
	define("POLLEN_CMS_VERSION","0.40");
	
	define("SLASH",strstr( PHP_OS, "WIN") ?"\\" : "/");
	
	$lSitePath = str_replace(SLASH.'core','',dirname(__FILE__));
	$lSitePath .= ( substr( $lSitePath, strlen( $lSitePath ) - strlen( SLASH ) ) === SLASH )?'':SLASH;
	define('SITE_PATH',$lSitePath);
	
	
	if(is_file(SITE_PATH.'sites'.SLASH.'multisites.conf.php')){
		include(SITE_PATH.'sites'.SLASH.'multisites.conf.php');
		if(isset($tabMultiSites)){
			if(isset($tabMultiSites[$_SERVER['SERVER_NAME']]))
				define('SITENAME',$tabMultiSites[$_SERVER['SERVER_NAME']]['SITENAME']);
			else
				die('SITE NOT FOUND, please check your file multisites.conf.php');
			unset($tabMultiSites);
		}
	}
	if(!defined('SITENAME')) define('SITENAME',false);
	define("SITE",'sites'.(SITENAME?SLASH.SITENAME:''));
	
	//Config File
	require('lib/psiteconfigfile.php');
	define("CONFIG_DIR",SITE_PATH.SITE.SLASH.'config'.SLASH);
	if(is_file(CONFIG_DIR.'myconfig.php')){
		include(CONFIG_DIR.'myconfig.php');
	}	
	define('CONFIG_FILE',CONFIG_DIR.'config.ini');
	$configDescr = SITE_PATH.'core'.SLASH.'default_config.ini';
	$configDescr = is_file($configDescr)?$configDescr:false;
	$configFile = new PSiteConfigFile(CONFIG_FILE, $configDescr);
	if(!$configFile->parse())
		printFatalHtmlError();

	define('SITE_URL',$configFile->getDirectParam('SITE_URL'));
	
	define('PLUGINS_DIR',SITE_PATH.'plugins');
	define('PAGES','pages');
	define('PAGES_PATH',SITE_PATH.SITE.SLASH.PAGES.SLASH.'languages');
	
	define('PAGES_MODELS_PATH',SITE_PATH.SITE.SLASH.PAGES.SLASH."models");
	define('CACHE_DIR',SITE_PATH.SITE.SLASH.PAGES.SLASH."cache".SLASH);
	if(!defined('MEDIAS_PATH'))
		define('MEDIAS_PATH',SITE_PATH.SITE.SLASH.PAGES.SLASH.'medias');

	define('THEME_DIR',SITE.SLASH.'theme'.SLASH);
	define('THEME_URL',SITE_URL.((SLASH!='/')?str_replace(SLASH,'/',THEME_DIR):THEME_DIR));
	
	//SMARTY CONFIG
	define("SMARTY_DIR",'vendors'.SLASH.'php'.SLASH."smarty".SLASH);
	//CACHES
	define("SMARTY_CACHE_DIR",CACHE_DIR."smarty".SLASH);
	define("CACHE_HIST_DIR",CACHE_DIR.'history'.SLASH);
	
	define("USE_URLREWRITING",$configFile->getDirectParam("USE_URLREWRITING"));
	
	if(!defined("DEBUG"))
		define ("DEBUG",0);

	/*FILE MANAGER, IMAGE VIEWER*/
	define ('USE_GD',($configFile->getDirectParam('USE_GD')=="true")?1:0);//if use gd put 1 else 0
	
	if( !USE_GD )
		define ('CONVERT_PATH',$configFile->getDirectParam('CONVERT_PATH'));//if not use gd, path to convert executable
	
	if(!defined("IMAGE_FILTER")){
		define("IMAGE_FILTER","\\.gif$|\\.jpg$|\\.jpeg$");//wich files to make thumbs
	}

	define("TEXTEDIT_FILTER","\\.txt$|\\.php$|\\.css$|\\.txt$|\\.ini|\\.xml$|\\.js$");
	define("TEXTEDIT_WYSWYG","\\.htm$|\\.html$");

}
?>
