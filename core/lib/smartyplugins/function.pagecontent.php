<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {pagecontent} function plugin
 *
 * Type:     function<br>
 * Name:     googlesitemap<br>
 * Purpose:  initialize overlib
 * @link http://smarty.php.net/manual/en/language.function.popup.init.php {popup_init}
 *          (Smarty online manual)
 * @author   Mathieu Vilaplana <mathieu.vilaplana@gmail.com>
 * @param array
 * @param Smarty
 * @return string
 */

function smarty_function_pagecontent($params, &$site)
{
	global $oEventsManager;
	$strContent = $site->fetch(SITE_PATH.str_replace('/',SLASH,'core/templates/pagecontent.tpl'),$site->getCompiledId());

	doEventAction('getpagecontent',array(&$strContent,&$params,$site));
	
	return $strContent;
}

?>
