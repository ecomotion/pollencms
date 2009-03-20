<?php
	include '../../../core/config.inc.php';
	require_once(SITE_PATH.'/core/lib/pfile.php');
	require_once(SITE_PATH.'/core/lib/ppage.php');
	require(SITE_PATH.'/core/lib/localization.php');
	//the config file
	$oCurrentFile = new PFile(__FILE__);
	require_once($oCurrentFile->getParentPath().SLASH.'lib.searchengine.php');
	
	if(!synchroBase())
	{printError();}	
	else
		echo 'All rights !!';
?>