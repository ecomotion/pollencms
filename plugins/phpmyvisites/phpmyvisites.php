<?php
addSmartyPlugin('output','add_phpmyvisites_tag');
addPollenPlugin('addPluginsTabs','tabPhpMyVisitesConfig');

function tabPhpMyVisitesConfig($tabExtraPlugins){
	
	$oPlugin = new PPluginDir(dirname(__FILE__));
	$oConfigFile = &$oPlugin->oConfig;
	
	$tabExtraPlugins[]=array(
		'FRAG_NAME'=>'plugins_phpmyvisites',
		'TAB_NAME'=>_('Php My Visites'),
		'TAB_CONTENT'=>$oConfigFile->DisplayEditor()
	);	
	return true;
}

function add_phpmyvisites_tag($source, &$smarty)
{
	$oPlugin = new PPluginDir(dirname(__FILE__));
	$oConfigFile = &$oPlugin->oConfig;
		
	if( $oConfigFile->getDirectParam('PHP_MYVISITES_ACTIVATE') !== "true" ) return $source;
	
	$id = intval($oConfigFile->getDirectParam("PHP_MYVISITES_ID"));
	$strPhpmvUrl = $oConfigFile->getDirectParam("PHP_MYVISITES_URL");
	
	if($id!='' && $id > 0 && $strPhpmvUrl !='' && strstr($source,'</body>')!==FALSE){
		$strPageName = preg_replace('/^'.preg_quote(SITE_URL,'/~').'/','',$smarty->oPageCurrent->getUrl());
		$strphpmv = '
<!-- Add By PhpMyVisites Plugin  -->
	<div style="display:none">
		<a href="http://www.phpmyvisites.us/" title="phpMyVisites | Open source web analytics" onclick="window.open(this.href);return(false);">
		<script type="text/javascript">
		<!--
		var a_vars = Array();
		var pagename = "'.$strPageName.'";
		
		var phpmyvisitesSite = '.$id.';
		var phpmyvisitesURL = "'.$strPhpmvUrl.'/phpmyvisites.php";
		//-->
		</script>
		<script language="javascript" src="'.$strPhpmvUrl.'/phpmyvisites.js" type="text/javascript"></script>
		<object><noscript><p>phpMyVisites | Open source web analytics
		<img src="'.$strPhpmvUrl.'/phpmyvisites.php" alt="Statistics" style="border:0" />
		</p></noscript></object></a>
	</div>
<!-- End Add By PhpMyVisites Plugin --> 
		';
		return  str_ireplace('</body>',$strphpmv."\n".'</body>',$source);
	}
	return $source;
}

/*function _getPhpMyVisitesTagTpl($oConfigFile){
	if(!$oConfigFile->getParam('PHP_MYVISITES_TPL',$strTpl))
		return false;
	return $strTpl;	
}*/

?>