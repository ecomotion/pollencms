<?php
session_start();
include '../../../core/config.inc.php';
require(SITE_PATH.'core/lib/lib_functions.php');
require (SITE_PATH.'core/lib/lib_error.php');

if( isConnected() ){
	if(isset($_GET['action']) || isset($_POST['action'])){
		
		require (SITE_PATH.'core/lib/pplugindir.php');
		require (SITE_PATH.'core/lib/ppage.php');
		require (SITE_PATH.'core/lib/localization.php');
		
		setLocalePath(dirname(__FILE__).'/../locale','search_engine');
		
		$action = (isset($_GET['action'])?$_GET['action']:$_POST['action']);
		switch($action){
			case 'initdatabase':
				if(!init())
					printFatalHtmlError();
			break;
			default:
				printFatalHtmlError('Action Unknown');
			break;
		}
	}
}
else{printFatalHtmlError('You are not connected',505);}
die();

function init(){
	require('lib.searchengine.php');
	
	if(!createBase())
		return false;
			
	if(!synchroBase())
		return false;
		
	echo gettext('Data base has been populated successfully');
	
	return true;
}
?>