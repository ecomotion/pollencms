<?php
if(!defined("LIB_FUNCTIONS")){
define("LIB_FUNCTIONS",1);

function isConnected($strSiteName=false){
	if( !isset($_SESSION) )
		session_start();
	if( isset($_SESSION) && isset($_SESSION['CONNECTED']) ){
		if(isset($_SESSION['TIME'])){
			$time = $_SESSION['TIME'];
			$time = time()-$time;
			if( $time < $_SESSION['SESSION_TIME'] ){
				msUpdateTimeConnect();
				return true;
			}
		}
	}
	return false;
}

function msConnect($strLogin,$strPassword,&$configFile){
	$strRecordPass = $configFile->getDirectParam("PASSWD");
	if($strLogin===$configFile->getDirectParam("LOGIN") && ( md5($strPassword)===$strRecordPass || $strPassword===$strRecordPass) ){
		$_SESSION['CONNECTED']="true";
		$_SESSION['LOGIN']= $strLogin;
		
		$timeMax = $configFile->getDirectParam("SESSION_TIME");
		$timeMax = $timeMax?$timeMax*60:60*10;
		$_SESSION['SESSION_TIME']= $timeMax;
		
		msUpdateTimeConnect();
		return true;
	}
	$tabParams = &$configFile->getTabParams();
	foreach($tabParams as $k=>$v){
		if( is_array($v) && strstr($k,'ACCOUNT')!==false ){
			$strLoginConf = isset($v['LOGIN'])?$v['LOGIN']:false;
			$strPasswordConf = isset($v['PASSWORD'])?$v['PASSWORD']:false;
			if($strLoginConf && $strPasswordConf){
				if( $strLogin==$strLoginConf && (md5($strPassword)==$strPasswordConf || $strPassword==$strPasswordConf) ){
					$_SESSION['CONNECTED']="true";
					$_SESSION['LOGIN']= $strLogin;
					$timeMax = $configFile->getDirectParam("SESSION_TIME");
					$timeMax = $timeMax?$timeMax*60:60*10;
					$_SESSION['SESSION_TIME']= $timeMax;			
					$_SESSION['ACCOUNT_TYPE']= isset($v['TYPE'])?$v['TYPE']:'EDITOR';
					$_SESSION['ACCOUNT_INFO']=	$v;	
					msUpdateTimeConnect();
					return true;
				}
			}
		}
	}
	return setError(_('Bad login / password !!'));	
}

function msUpdateTimeConnect(){
	if( isset($_SESSION) ){
		$_SESSION['TIME']=time();
	}
}

function msDisconnect(){
	if(isset($_SESSION)){
		unset($_SESSION['CONNECTED']);
		unset($_SESSION['TIME']);
		unset($_SESSION['SESSION_TIME']);
		unset($_SESSION['LOGIN']);
		unset($_SESSION['ACCOUNT_TYPE']);
		unset($_SESSION['ACCOUNT_INFO']);
		session_destroy();
	}
}
function isSuperAdmin(){
	if( isset($_SESSION) ){
		if( isset($_SESSION['ACCOUNT_TYPE']) && $_SESSION['ACCOUNT_TYPE']=='ADMIN' )
			return true;
	}
	return false;
}
function getUserEditorBar(){
	if(!isConnected())
		return false;
	
	$strBar = (isset($_SESSION['ACCOUNT_INFO']) && isset($_SESSION['ACCOUNT_INFO']['FCK_BAR']))?$_SESSION['ACCOUNT_INFO']['FCK_BAR']:false;
	if(!$strBar)
		global $configFile;
	if(!$strBar && !$configFile->getParam('FCK_BAR',$strBar))
		$strBar = false;
	return $strBar;
}

function deleteMenuCache(){
	//on supprime le cache du menu
	if(is_file(SMARTY_CACHE_DIR.'menu.cache')){
		$menuFile = new PFile(SMARTY_CACHE_DIR.'menu.cache');
		if( !$menuFile->Delete() )
			return false;
	}
	return true;
}

	function pcms_clearcache($strType='site'){
		$cacheDir=false;
		switch($strType){
			case 'site':
				$cacheDir = new PDir(SMARTY_CACHE_DIR);
				break;
			case 'thumbs':
				$cacheDir = new PDir(CACHE_DIR.'thumbnails/');
				break;
			case 'history':
				$cacheDir = new PDir(CACHE_HIST_DIR);
				break;
			default:
				$cacheDir = new PDir(SMARTY_CACHE_DIR);
				break;		
		}
		
		if(is_dir($cacheDir->path) && !$cacheDir->Delete())
			return false;setError(_('Error deleting cache.'));

		return true;
	}

function urljsencode($str){
	return (SLASH == '/')?urlencode($str):urlencode(str_replace(SLASH,'/',$str));
}
function urljsdecode($str){
	return (SLASH == '/')?urldecode($str):str_replace('/',SLASH,urldecode($str));
}



}//END DEFINE
?>
