<pre>
<?php
include('core/config.inc.php');
require(SITE_PATH.'core/lib/pdircategory.php');
require (SITE_PATH.'core/lib/localization.php');


/**
 * Enter description here...
 *
 * @param unknown_type $strDirSum
 * @param unknown_type $oDir
 */
function checkDir($strDirSum,&$oDir){
	echo _('Checking '.$strDirSum.' directory ....');
	//if directory not exists, try to create it
	if(!$oDir->isDir()){
		if(!$oDir->mkdir())
			printFatalError();
	}
	//check if directory is writable
	if(!is_writable($oDir->path))
		printFatalError('[FALSE] '.sprintf(_('%s directory %s is not writable. Please Check permissions.'), $strDirSum, $oDir->getRelativePath()));
	echo "[ OK ]\n";
}

//check write permissions on PAGES PATH
checkDir('main web site pages', new PDir(PAGES_PATH));
checkDir('Cache', new PDir(CACHE_DIR));
checkDir('Medias', new PDir(MEDIAS_PATH));

echo _('Checking SITE_URL variable ....');
$strSiteUrl = str_replace(basename(__FILE__),'', $_SERVER['REQUEST_URI']);
if( $strSiteUrl != SITE_URL ){
	printFatalError(sprintf(_('
Site Url is: %s, while it should be %s.
Please change it in the web site config file: %s'),SITE_URL,$strSiteUrl, $configFile->getRelativePath()));
}
echo "[ OK ]\n";

echo _('Checking url rewriting ....');
$oFile = new PFile(SITE_PATH.'.htaccess');
if(!is_file($oFile->path)){
	echo "[ !!!! WARNING !!!! ]\n";
	echo _('No .htaccess file has been found, you should rename the htaccess.txt file in .htaccess to active the url rewriting');
}else{
	//check the rewrite base match with the SITE_URL
	$strContent = file_get_contents($oFile->path);
	if(!preg_match('/(RewriteBase)([^\\n]*)/',$strContent,$tabMatch))
		printError('Can not find the rewritebase');
	else{
		$strBase = trim($tabMatch[2]);
		if($strBase != SITE_URL){
			printFatalError(sprintf(_('RewriteBase is: %s, while it should be %s.
Please change it int the .htaccess file.
			'),$strBase, SITE_URL));
		}
	}
	echo "[ OK ]";
}

echo '

###############################################################################
Your installation seems to be good.
You should now remove the '.basename(__FILE__).' file to access your web site.
';
?>
</pre>