<?php
/**
 * Smarty plugin Insert Menu
 * @usage insert_menu() => return the whole structure in ul>li
 * @package pollencms
 * @subpackage plugins
 */


/**
 * smarty_insert_menu
 * Smarty plugin that parse the menu and generate the html menu.
 *
 * @param $params an array of params
 * LEVEL_STOP: the last level to expand, default -1, means no limit
 * LEVEL_START: the level to start in the three, default -1, means parse the whole three
 * NBFILE_MIN_PERCAT: the minimum number of subsection befor display the subsection,
 * for example if only one subsection exists, do not show it.
 * CLASS_SELECTED:the css class to apply to the current item
 * BLOCK_TYPE: the main container of a section, by default ul
 * ELEM_TYPE: the container of a sub section
 * ELEM_SEPARATOR: the elem to add after each sub section element
 * @param smarty object $smarty, a reference to the current smarty
 * @return a string of the menu in html
 */
function smarty_insert_menu($params, &$smarty)
{
	
	$ilevelStop = isset($params['LEVEL_STOP'])?$params['LEVEL_STOP']:-1;
	$ilevelStart = isset($params['LEVEL_START'])?$params['LEVEL_START']:-1;
	$iNbMiniPerCat = isset($params['NBFILE_MIN_PERCAT'])?$params['NBFILE_MIN_PERCAT']:0;
	$strClassSelected = isset($params['CLASS_SELECTED'])?$params['CLASS_SELECTED']:'selected';
	$strBlockType = isset($params['BLOCK_TYPE'])?$params['BLOCK_TYPE']:'ul';
	$strElemType = isset($params['ELEM_TYPE'])?$params['ELEM_TYPE']:'li';
	$strElemSeparator = isset($params['ELEM_SEPARATOR'])?$params['ELEM_SEPARATOR']:'';
	$bExpand = isset($params['EXPAND'])?$params['EXPAND']:true;
	
	$tabMenu = $smarty->get_template_vars('menu');
	if(!isset($tabMenu)) $tabMenu=$smarty->getMenu();
	
	return simGetMenu($smarty->oPageCurrent->getId(), $tabMenu, $ilevelStop, $ilevelStart, $iNbMiniPerCat, $bExpand, $strClassSelected, $strBlockType, $strElemType, $strElemSeparator);

}

/**
 * getMenu
 *
 * @param unknown_type $strCurrId
 * @param unknown_type $tabMenu
 * @param unknown_type $ilevelStop
 * @param unknown_type $ilevelStart
 * @param unknown_type $iNbMiniPerCat
 * @param unknown_type $bExpand
 * @param unknown_type $strClassSelected
 * @param unknown_type $strBlockType
 * @param unknown_type $strElemType
 * @param unknown_type $strElemSeparator
 * @param unknown_type $iCurrentLevel
 * @param unknown_type $bSonVisible
 * @return unknown
 */
function simGetMenu($strCurrId, &$tabMenu, $ilevelStop=-1, $ilevelStart=-1, $iNbMiniPerCat=0, $bExpand=false,$strClassSelected, $strBlockType, $strElemType, $strElemSeparator, $iCurrentLevel=1,$bSonVisible=false){
	$strReturn = '';
	//on ne parcours le menu que si il comprend plus d'éléments que le minimum requis
	if(sizeof($tabMenu)>$iNbMiniPerCat){
		$bLevelTest = ($iCurrentLevel>=$ilevelStart && ($iCurrentLevel<=$ilevelStop || $ilevelStop < 1));
		$strReturn .= ($bLevelTest)?"\n".simRepeatString("\t",($iCurrentLevel-1)).'<'.$strBlockType.' id="menuLevel'.$iCurrentLevel.'" >'."\n":'';
		foreach($tabMenu as $elemMenu){
			$strSelected = ( strstr($strCurrId, $elemMenu['ID']) !== false )?' class="'.$strClassSelected.'"':'';
			$localSonVisible = ($strSelected!='')?true:$bSonVisible;
			$bParseMenu =  $bLevelTest || $strSelected != '' || $bSonVisible || ($bPrintFirstLevel && $iCurrentLevel==1);
			$bPrintMenuElem =  $bParseMenu && $bLevelTest;

			if( $bParseMenu ){
				if($bPrintMenuElem){
					$strReturn .= simRepeatString("\t",$iCurrentLevel);
					$strReturn .= '<'.$strElemType.' '.$strSelected.'>';
					$strReturn .= '<a id="'.$elemMenu['ID'].'" '.$strSelected.' href="'.$elemMenu['URL'].'">'.$elemMenu['NAME'].'</a>';
				}

				if( ($bExpand  || $strSelected != '') && isset($elemMenu['SUBMENU']) && ($ilevelStop < 0 || $iCurrentLevel < $ilevelStop) ){
					$strReturn .= simGetMenu($strCurrId, $elemMenu['SUBMENU'], $ilevelStop, $ilevelStart, $iNbMiniPerCat, $bExpand,$strClassSelected, $strBlockType, $strElemType,$strElemSeparator, ($iCurrentLevel+1),$localSonVisible);
					if($bPrintMenuElem)
					$strReturn .= simRepeatString("\t", $iCurrentLevel);
				}
					
				if($bPrintMenuElem)
				$strReturn .= '</'.$strElemType.'>'.$strElemSeparator."\n";
			}
		}
		if(strlen($strElemSeparator)>0)
		$strReturn = substr($strReturn, 0, -strlen($strElemSeparator));
		$strDivClear = '';
		$strReturn .= $bLevelTest?simRepeatString("\t",($iCurrentLevel-1)).'</'.$strBlockType.'>'."\n":'';
	}

	return $strReturn;
}

/**
 * simRepeatString
 * repeat a string and return the result. Useful for print for example 4 tabs (\t)
 *
 * @param string $strString, the string to repeat
 * @param int $iNb, how many time to repeat the string
 * @return the string repeated.
 */
function simRepeatString($strString,$iNb){
	$strReturn = '';
	for($i=0;$i<$iNb;$i++){
		$strReturn .= $strString;
	}
	return $strReturn;
}

?>
