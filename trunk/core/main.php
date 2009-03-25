<?php
/**
 * FILE: MAIN.PHP
 * This is the main of the cms core.
 * Build the site object and if page exists display it.
 *
 * If the page is sitemap.xml, disable cache and display the specific google site map template
 * If the page is not found, try to find the 404.html file in the site dir 
 * if not found redirect index of the web site (SITE_URL).
 * 
 * Copyright 2005, 2008 Mathieu Vilaplana
 */
$timestart = time()+microtime();

require 'config.inc.php';
require 'lib/lib_error.php';
require 'lib/pollencms.php';
require 'lib/ppage.php';
require 'lib/plink.php';

if(is_file(SITE_PATH.'checkinstall.php'))
	header('location:checkinstall.php');

	
$site = new PPollenCMS();
$site->loadExtraFilters();

//if no page selected, try to load the first page
if(!isset($_GET["page"])){
	//if no page was selected, take the first page
	$oPage=$site->getFirstPage($dirSearch=false,isConnected());
	
}else {
	//get the page as parameter, decode it and replace - by \s
	$page =stripslashes(rawurldecode($_GET["page"]));
	/**
		EXTRA PAGES
	*/
	switch($page){
		case 'config.js':
			header ('Content-Type: text/javascript;');
			header("Cache-Control: no-cache");//sinon bug ie
			$site->loadDefaultFilters();
			$site->display(SITE_PATH.'core'.SLASH.'templates'.SLASH.'config_js.tpl');
			die();
		case 'fcktemplates.xml':
			header('content-type: text/xml');
			$site->loadDefaultFilters();
			$site->display(SITE_PATH.'core'.SLASH.'templates'.SLASH.'fcktemplates.tpl');
			die();		
		case 'fckconfig.js':
			header ('Content-Type: text/javascript;');
			header("Cache-Control: no-cache");//sinon bug ie
			$site->loadDefaultFilters();
			$site->display(SITE_PATH.'core'.SLASH.'templates'.SLASH.'fckconfig.tpl');
			die();		
		case 'fckstyles.xml':
			header('content-type: text/xml');
			$site->loadDefaultFilters();
			$site->display(SITE_PATH.'core'.SLASH.'templates'.SLASH.'fckstyles.tpl');
			die();		
	}
	/*for safety reasons, if user try to go out pages dir, go to first page*/
	if(strstr('..',$page)){$oPage=$site->getFirstPage();}
	else {$oPage=$site->getPage($page, isConnected());}
	
}


//if page has not been found, load the extra page event
if(!$oPage){
	$bRes = doEventAction('extrapage', array(&$page, &$site));
	if($bRes !==true)
		$oPage = &$bRes; 
}

if(!$oPage){
	header("HTTP/1.0 404 Not Found");
	die(_('Page not found'));
}


//display the page
$site->displayPage($oPage);
setDebug('Generate page in '.(time()+microtime()-$timestart).' s');
//var_dump($oGlobalEventsManager->_events);
if(DEBUG)
	printDebug();
?>