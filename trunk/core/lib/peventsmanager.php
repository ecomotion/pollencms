<?php
/**
 * 12/11/2008
 * Add the smarty insert function as a pollen plugin
 */

if(!defined("PEVENTSMANAGER")){
define("PEVENTSMANAGER",1);
require(SITE_PATH.'core/lib/pplugindir.php');

class PEventsManager {
	
	var $_tabAvailableEvents  = array(
		/* Smarty Filters */
		'function','insert','output','pre','post',
		/* Pollen Plugins */
		'file_menu','savepage','renamepage','deletepage','extrapage','getpagecontent','adminmainmenu','uploadfile', 'beforedisplay',
		'addPluginsTabs','adminHeader'
	);
	
	var $_events;//array to record the events recorded
	
	function PEventsManager(){
		$this->_events = null;
	}
	
	
	function _loadAllEvents(){
		if(is_dir(PLUGINS_DIR)){
			$oPdir = getFileObject(PLUGINS_DIR);

			//parse each plugin directory
			$listDir = $oPdir->listDir($oPdir->ONLY_DIR);
			foreach($listDir as $strPluginPath){
				$oDirPlugin = new PPluginDir($strPluginPath);
				if( $oDirPlugin->isActivated() ){
					$listFiles = $oDirPlugin->listDir($oDirPlugin->ONLY_FILES,$bFullPath=false);
	
					//parse each files and add it in the tab events
					foreach($listFiles as $strFileName){
						if(preg_match('/(.+)filter\.(.+)\.php/',$strFileName,$tabInfo)){
							$this->_events['smarty'][$tabInfo[1]][]=array('FILEPATH'=>$oDirPlugin->path.SLASH.$strFileName,'NAME'=>$tabInfo[2],'EVENT'=>$tabInfo[1]);
						}
						else if(preg_match('/insert\.(.+)\.php/',$strFileName,$tabInfo)){
							$this->_events['smarty']['insert'][]=array('FILEPATH'=>$oDirPlugin->path.SLASH.$strFileName,'NAME'=>$tabInfo[1],'EVENT'=>'insert');
						}
						else if(preg_match('/function\.(.+)\.php/',$strFileName,$tabInfo)){
							$this->_events['smarty']['function'][]=array('FILEPATH'=>$oDirPlugin->path.SLASH.$strFileName,'NAME'=>$tabInfo[1],'EVENT'=>'function');
						}
						else if(preg_match('/(.+)event\.(.+)\.php/',$strFileName,$tabInfo)){
							$strEvent=$tabInfo[1];
							$strName = $tabInfo[2];
							$strFunction = 'pollen_'.$strEvent.'event_'.$strName;
							require_once($oDirPlugin->path.SLASH.$strFileName);
							$this->_addEvent($strEvent,$strFunction,$pritority=10);
						}else if(preg_match('/\.php$/',$strFileName)){
							require_once($oDirPlugin->path.SLASH.$strFileName);
						}
					}					
				}//end if plugin is activated
			}			
		}
	}
	
	function _getEventType($strEvent){
		$strType='';
		switch($strEvent){
			case 'insert':
			case 'output':
			case 'pre':
			case 'post':
				$strType = 'smarty';
				break;
			default:
				$strType='pollen';
				break;
		}	
		return $strType;
	}
	
	function getEvents($strEvent, &$tabReturn){
		if(!in_array($strEvent,$this->_tabAvailableEvents))
			return setError(__('Event not exists').' '.$strEvent);
		
		if(!$this->_events)
			$this->_loadAllEvents();
		
		$strType = $this->_getEventType($strEvent);
		if( !isset($this->_events[$strType]) || !isset($this->_events[$strType][$strEvent]) ){
			$tabReturn = array();
			return true;
		}
		if($strType == 'smarty')
			$tabReturn = $this->_events[$strType][$strEvent];

		//for pollen events, load the events and order by priority
		else if($strType == 'pollen'){
			$tabNotOrdered = array();
			foreach($this->_events[$strType][$strEvent] as $event){
				if(function_exists($event['FUNCTION']))
					$tabNotOrdered[$event['PRIORITY']][] = $event['FUNCTION'];
			}
			krsort($tabNotOrdered);//on trie le tableau par priorité
			$tabReturn = array();
			foreach($tabNotOrdered as $priority=>$tabFunctions){
				foreach($tabFunctions as $strFct)
					$tabReturn[] =$strFct; 
			}
		}
		return true;
	}
	
	function getSmartyExtraFilters(){
		if(!$this->_events){
			$this->_loadAllEvents();
		}
		
		if(!isset($this->_events['smarty']))
			return array();
		return $this->_events['smarty'];
	}

	function doAction($strActionName, $params){
		if(!$this->getEvents($strActionName,$tabEvents))
			return false;
		switch($strActionName){
			case 'extrapage':
				foreach($tabEvents as $eventFunct){
					$bResult = call_user_func_array($eventFunct,$params);
					if($bResult === true)
						die();
					if($bResult !== false){
						return $bResult;
						break;
					}
				}					
			break;
			case 'adminHeader':
			case 'adminmainmenu':
				foreach($tabEvents as $eventFunct){
					if(($bResult = call_user_func_array($eventFunct,$params))=== false)
						return false;
					else if($bResult !== true)
						echo $bResult;
				}
			break;
			default:
				foreach($tabEvents as $eventFunct){
					if(($bResult = call_user_func_array($eventFunct,$params))=== false)
						return false;
				}
			break;
		}
		return true;
	}
	
	function _addEvent($strEventName,$strFunctionName,$pritority=10){
		if(!function_exists($strFunctionName))
			return setError(sprintf(_('Try to add event %s but function %s not exists.'),$strEventName,$strFunctionName));
		if(!in_array($strEventName,$this->_tabAvailableEvents))
			return setError(sprintf(_('Event %s not exists'),$strEventName));

		$strEventType = $this->_getEventType($strEventName);
		
		if( $strEventType == 'pollen' ){
			$this->_events[$strEventType][$strEventName][]=array(
				'EVENT'=>$strEventName,
				'FUNCTION'=>$strFunctionName,
				'PRIORITY'=>$pritority
			);
		}
		return true;
	}
	
	/**
	 * _addFilter
	 * Add smarty filter to the pollen event manager
	 * @param string $strType, type of the filter
	 * @param string $strFunctionName
	 * @param unknown_type $strTplFctName
	 * @return true if succeed
	 */
	function _addFilter($strType,$strFunctionName, $strTplFctName=null){
		if(!function_exists($strFunctionName))
			return setError(sprintf(_('Try to add event %s but function %s not exists.'),$strType,$strFunctionName));
		if(!in_array($strType,$this->_tabAvailableEvents))
			return setError(__('Filter not exists').' '.$strType);
		
		$this->_events['smarty']['$strEventName'][]=array(
			'TPL_FCT_NAME'=>$strTplFctName,/*only use in function filter*/
			'FUNCTION'=>$strFunctionName,
			'TYPE'=>$strType
		);
		return true;
	}
}//end class
	
	/**
	 * doEventAction
	 *
	 * @param string $strActionName
	 * @param array $params
	 * @return true if suceed, else return false
	 */
	function doEventAction($strActionName, $params){
		global $oGlobalPluginManager;
		return $oGlobalPluginManager->doAction($strActionName,$params);
	}
	
	/**
	 * addPollenPlugin
	 * To add a plugin to pollencms
	 * 
	 * @param string $strEventName
	 * @param string $strFunctionName
	 * @param int $iPriority, the order to exec the plugin, bu default 10
	 * @return true if succeed, else return false
	 */
	function addPollenPlugin($strEventName,$strFunctionName,$iPriority=10){
		global $oGlobalPluginManager;
		return $oGlobalPluginManager->_addEvent($strEventName,$strFunctionName,$iPriority);
	}
	
	/**
	 * addSmartyPlugin
	 * Add a Smarty Plugin to pollen cms
	 *
	 * @param string $strFilterType
	 * @param string $strFunctionName
	 * @param string $strTplFctName
	 * @return true if succeed, else return false.
	 */
	function addSmartyPlugin($strFilterType,$strFunctionName, $strTplFctName=null){
		global $oGlobalPluginManager;
		return $oGlobalPluginManager->_addFilter($strFilterType,$strFunctionName,$strTplFctName);
	}
	
	/**
	 * getSmartyExtraFilters
	 * Return a table of smarty extra filters define by the user.
	 * 
	 * @return array of smarty extra filters
	 */
	function getSmartyExtraFilters(){
		global $oGlobalPluginManager;
		return $oGlobalPluginManager->getSmartyExtraFilters();
	}
	
	$oGlobalPluginManager = new PEventsManager();
	
	
}//end define
?>