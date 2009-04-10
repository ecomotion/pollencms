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

function smarty_outputfilter_copyleft($source, &$smarty)
{
	global $configFile;
	$strCopyLeft = '
	<div id="pollenCopyLeft" >based on <a href="http://www.pollencms.com" target="_blank" title="Content Management System" >Pollen CMS</a></div>
';	

	return  str_ireplace('</body>',$strCopyLeft.'</body>',$source);
}
?>