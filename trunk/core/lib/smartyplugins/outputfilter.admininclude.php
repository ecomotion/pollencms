<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Fichier :  outputfilter.addconf.php
 * Type :     filtre de post traitement
 * Nom :      addconf
 * Rôle :     Ajoute les scripts nécessaires à l'admin aprés le header.
 * ----------
 */

function smarty_outputfilter_admininclude($source, &$smarty)
{
	global $configFile;
	$strNewHead = '
	<!--begin scriptsadmin -->
		<script type="text/javascript" src="'.SITE_URL.'?page=config.js" ></script>
		<script type="text/javascript" src="'.SITE_URL.'vendors/jscripts/jquery.js" ></script>
		<script src="'.SITE_URL.'vendors/jscripts/jqueryplugins/jquery.hotkeys.js"></script>
		<script src="'.SITE_URL.'core/jscripts/admin.js"></script>
	
	';	

	$strUiTheme = $configFile->getDirectParam('UI_THEME');
	$strNewHead.= '<link rel="stylesheet" href="'.SITE_URL.'vendors/jscripts/jqueryui/themes/'.$strUiTheme.'/jquery-ui-1.7.1.css" />';
	
	$strNewHead .= '
	<link rel="stylesheet" href="'.SITE_URL.'core/admin/theme/css/admin_mode.css" type="text/css" media="all" />
	
	<script language="JavaScript" >
			var current_page_path = "'.$smarty->oPageCurrent->getRelativePath().'";';

	if( isset($_GET['admin']) ){
		$strNewHead.='
			$(function(){
				var fctLoad=function(){
					loadJS($tabScriptsToLoadDialog, initDialog);return;
				}
				var fct = function(){setTimeout(fctLoad,400);}
				window.onload=fct;
			});
		';
	}
	$strNewHead .= '
	</script>
	<!--end scriptsadmin-->
	';
	return  preg_replace('/(<head[^>]?>)/','\1'.$strNewHead,$source);
}
?>