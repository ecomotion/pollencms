<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {popup_init} function plugin
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

function smarty_function_page_guidage($params, &$smarty)
{
	$separator=isset($params['separator'])?$params['separator']:'/';
	$strReturn = '';
	$oFirstPage = $smarty->getFirstPage();
	$oFirstPageUrl = $oFirstPage->getUrl();
	$strHomeName=$oFirstPage->getMenuName();

	if($strHomeName=='index')
		$strHomeName='Home';
	if(isset($params['homename']))
		$strHomeName=$params['homename'];
	
	$strReturn .= '<a href="'.$oFirstPageUrl.'">'.$strHomeName.'</a>';
	
	$tabGuidage=$smarty->oPageCurrent->getTabGuidage();	
	foreach($tabGuidage as $elemGuidage){
		if($oFirstPageUrl!=$elemGuidage['URL'])
			$strReturn .= ' '.$separator.' <a href="'.$elemGuidage['URL'].'">'.$elemGuidage['NAME'].'</a>';
	}
	return $strReturn;
}

?>
