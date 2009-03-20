<?php
if(!defined("LIB_LOCALIZATION")){
	
	define("LIB_LOCALIZATION",1);

	include (SITE_PATH.'core/config.inc.php');
	require(SITE_PATH.'vendors/php/phpgettext/gettext.inc.php');
	
	function setLocalePath($strPath=false,$strDomain=false){
		$strDomain = (!$strDomain)?'messages':$strDomain;
		$strPath = (!$strPath)?SITE_PATH.'core/locale':$strPath;
		if(!is_dir($strPath))
			return setError(sprintf('Can not change the local path to %s. Directory not exists'),$strPath);

		global $configFile;
		$locale = $configFile->getDirectParam('USER_LANGUAGE');
	
		T_setlocale(LC_ALL, $locale);
	    
		bindtextdomain($strDomain, $strPath);
		
	
		// bind_textdomain_codeset is supported only in PHP 4.2.0+
		if (function_exists('bind_textdomain_codeset')) 
	  		bind_textdomain_codeset($strDomain, 'utf-8');
			
		textdomain($strDomain);
	  	
		return true;
	}
	
	function mygetText($strWord){
		return _($strWord);
	}

	
	setLocalePath();
	
}//end define
?>