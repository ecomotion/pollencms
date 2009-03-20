<?php
if (isset($_POST["PHPSESSID"])) {
	session_id($_POST["PHPSESSID"]);
}
session_start();
include '../config.inc.php';
require(SITE_PATH.'core/lib/lib_functions.php');
require (SITE_PATH.'core/lib/lib_error.php');


if( isConnected() ){
	
	if(isset($_GET['action']) || isset($_POST['action'])){
		
		require SITE_PATH.'core/lib/pollencms.php';
		require SITE_PATH.'core/lib/pfile.php';
		require SITE_PATH.'core/lib/ppage.php';
		require SITE_PATH.'core/lib/plink.php';
		require SITE_PATH.'core/lib/pimage.php';
		
		$action = (isset($_GET['action'])?$_GET['action']:$_POST['action']);
		
		switch($action){

			case 'sort':
				if(!sortfiles($_REQUEST['filename']))
					printError();
				break;

			case 'rename':
				if(!renamefile(urldecode($_POST['filename']), stripslashes($_POST['value'])))
					printError();
				break;

			case 'gettext':
				ajaxGetText($_POST['text']);
				break;
			case 'savepage':
				if(!savepage())
					printFatalHtmlError();
				break;
			case 'savefile':
				if(!savefile())
					printFatalHtmlError();
				break;
			case 'savesiteconfig':
				if(!savesiteconfig())
					printFatalHtmlError();
				break;
			case 'loadhistorypage':
				loadHistoryPage($_REQUEST['strPage']);
				break;
				
			case 'clearcache':
				clearCache((isset($_POST['type'])?$_POST['type']:''));
				break;
			case 'upload':
				if(!upload())
					printError();
				break;
			case 'createdir':
				if(!createdir()){
					printFatalHtmlError();
				}
				break;
			case 'createfile':
				if(!createfile()){
					printFatalHtmlError();
				}
			break;
			case 'createlink':
				if(!createlink()){
					printFatalHtmlError();
				}
			break;
			case 'deletefile':
				if(!deletefile())
					printFatalHtmlError();
			break;
			case 'copyfile':
				if(!copyfile())
					printFatalHtmlError();
			break;
			case 'movefile':
				if(!movefile())
					printFatalHtmlError();
			break;
			case 'setpageconfigvar':
				if(!SetPageConfigVar())
					printFatalHtmlError();
			break;
			case 'resizeimage':
				if(!resizeimage())
					printFatalHtmlError();
				break;
			case 'toggleactivateplugin':
				if(!toggleactivateplugin())
					printFatalHtmlError();
				break;
			default:
				printFatalHtmlError('Action Unknown');
			break;

		}
	}

}//must be connected;
else{printFatalHtmlError('You are not connected',505);}
die();


function toggleactivateplugin(){
	global $configFile;
	if(!isset($_REQUEST["plugin"]) || !isset($_REQUEST["value"]))
		setError('Internal Error in togleactivateplugin');
	
	$strPluginName = urljsdecode($_REQUEST["plugin"]);
	$strValue = urljsdecode($_REQUEST["value"]);
	$oPluginDir = new PPluginDir(PLUGINS_DIR.SLASH.$strPluginName);
	if(!$oPluginDir->isDir())
		return setError(sprintf(_('Can not find the plugin %s'),$strPluginName));
	
	if(!$oPluginDir->toggleActivate(true))
		return false;

	if($strValue == "true")
		echo sprintf(_('Plugin %s is activated'),$oPluginDir->getPluginName());
	else
		echo sprintf(_('Plugin %s is unactivated'),$oPluginDir->getPluginName(),true);
	
	return true;
}

/**
 * SavePage
 * 
 * @return true if succeed, else return false
 */
function savepage(){
	if(!isset($_REQUEST["file"]) || $_REQUEST["file"]=="" || eregi("\.\.",$_REQUEST["file"])) {
		return setError(_("You should specified a file to save"));
	}
	$pfile = &getFileObject(SITE_PATH.urljsdecode($_REQUEST["file"]));
	if(!$pfile)
		return setError('Internal Error, can not save page, file not found.');

	if(isset($_REQUEST["textConfig"])){
		if(!$pfile->Save($_REQUEST["text"], $_REQUEST["textConfig"]))
			return false;
	}else {
		if(!$pfile->Save($_REQUEST["text"]))
			return false;
	}
	echo _('Page has been saved.');
	return true;
}

function savefile(){
	if(!isset($_REQUEST["file"]) || $_REQUEST["file"]=="" || eregi("\.\.",$_REQUEST["file"])) {
		return setError(_("You should specified a file to save"));
	}
	$pfile = &getFileObject(SITE_PATH.urljsdecode($_REQUEST["file"]));
	if(!$pfile)
		return setError('Internal Error, can not save file, file not found.');
	if(!$pfile->Save($_REQUEST['text']))
		return false;
	echo _('File saved successfully.');
	return true;
}

function savesiteconfig(){
	if(!isset($_REQUEST['text']))
		return setError('Internal Error in savesiteconfig.text is not defined');
	
	$strText = stripslashes($_REQUEST['text']);
	$oIniFileTmp = new PConfigfile(CACHE_DIR.'tmpconfig.ini');
	
	if(!$oIniFileTmp->Save($strText))
		return false;
	if(!$oIniFileTmp->parse())
		return false;
	$tabNewParams = $oIniFileTmp->getTabParams();
	$oIniFileTmp->Delete();
	
	global $configFile;
	foreach($tabNewParams as $k=>$v){
		$configFile->setParam($k,$v);
	}
	if(!$configFile->Save())
		return false;
	
	echo _("Site configuration has been saved !");
	return true;
}

function upload(){
	if( !isset($_POST['CURRENT_DIR']) )
		return setError('Internal error, CURRENT_DIR is not set');
	$strCurrDir = urldecode($_POST['CURRENT_DIR']);
	
	if( !($oDir = &getFileObject(SITE_PATH.SLASH.$strCurrDir)) || !is_dir($oDir->path) )
	 return setError(sprintf(_('Internal error, directory %s not exists.'),$strCurrDir)); 

	if( !$oDir->uploadFile($_FILES, 'Filedata') )
		return false;
	
	$oFileUploaded = &getFileObject($oDir->path.SLASH.$oDir->getUnixName($_FILES['Filedata']['name']));
	if($oFileUploaded && $oFileUploaded->is_image()){
	  	if( !$oFileUploaded->createThumb(70) )
	  		return false;
		if( !$oFileUploaded->createThumb(480, false) )
			return false;
	}
	if($oFileUploaded && !doEventAction('uploadfile',array(&$oFileUploaded)))
		return false;
	
	echo 'OK';
	return true;
}

function createdir(){
	if( !isset($_POST['CURRENT_DIR']) )
		return setError('Internal error, CURRENT_DIR is not set');
	$strCurrDir = urljsdecode($_POST['CURRENT_DIR']);
	if( !($oDir = &getFileObject(SITE_PATH.$strCurrDir)) || !is_dir($oDir->path) )
	 return setError(sprintf(_('Internal error, directory %s not exists.'),$strCurrDir)); 
	
	$strNewDir = isset($_POST['NEW_DIR'])?stripslashes($_POST['NEW_DIR']):'';

	
	if(!$oDir->createDir($strNewDir))
		return false;
	
	return true;
}

function createfile(){
	
	if( !isset($_POST['CURRENT_DIR']) )
		return setError('Internal error, CURRENT_DIR is not set');
	$strCurrDir = urljsdecode($_POST['CURRENT_DIR']);
	
	if( !($oDir = &getFileObject(SITE_PATH.$strCurrDir)) || !is_dir($oDir->path) )
	 return setError(sprintf(_('Internal error, directory %s not exists.'),$strCurrDir)); 
	 
	$strNewFile = isset($_POST['NEW_FILE'])?stripslashes($_POST['NEW_FILE']):'';

	if(!$oDir->createFile($strNewFile))
		return false;
	
	return true;
}

function createlink(){
	if( !isset($_POST['CURRENT_PAGE']) ){
		return setError('Internal error, CURRENT_PAGE is not set');
	}
	$strCurrPagePath = SITE_PATH.urljsdecode($_POST['CURRENT_PAGE']);
	if( !($oPage=getFileObjectAndFind($strCurrPagePath))){
		return setError(sprintf('Internal error, File not found: %s.',urldecode($_POST['CURRENT_PAGE'])));
	}
	if(!$oPage->createlink())
		return false;
	
	return true;	
}

function deletefile(){
	if( !isset($_POST['FILE_RELATIVE_PATH']) )
		return setError('Internal error in delete, FILE_RELATIVE_PATH is not set');
	
	$strFile = urldecode($_POST['FILE_RELATIVE_PATH']);
	if( !($oFile = &getFileObject(SITE_PATH.$strFile)) )
		return setError(sprintf(_('Internal error, file object %s not exists.'),$strFile)); 
	
	if( !$oFile->delete() )
		return false;

	 return true;
}

function copyfile(){
	if( !isset($_POST['FILE_RELATIVE_PATH']) )
		return setError('Internal error in delete, FILE_RELATIVE_PATH is not set');
	
	$strFile = urljsdecode($_POST['FILE_RELATIVE_PATH']);
	if( !($oFile = &getFileObject(SITE_PATH.$strFile)) )
		return setError(sprintf(_('Internal error, file object %s not exists.'),$strFile)); 
	
	if( !$oFile->Copy(stripslashes(urldecode($_POST['COPY_NAME']))) )
		return false;

	 return true;
}

function renamefile($strFilePath, $strNewName){
	if( !($pFile = &getFileObjectAndFind(SITE_PATH.$strFilePath)) ){
		return setError(sprintf(_('Internal error, file object %s not exists.'),$strFilePath)); 
	}
	if(!$pFile->Rename($strNewName))
		return false;
	echo $strNewName;
	return true;
}

function SetPageConfigVar(){
	if( !isset($_POST['FILE_RELATIVE_PATH']) )
		return setError('Internal error in Set Page Config Var, FILE_RELATIVE_PATH is not set');
	if( !isset($_POST['VAR_NAME']) )
		return setError('Internal error in Set Page Config Var, VAR_NAME is not set');
	if( !isset($_POST['VAR_VALUE']) )
		return setError('Internal error in Set Page Config Var, VAR_VALUE is not set');
	
	$strVarName = urldecode($_POST['VAR_NAME']);
	$strVarValue = urldecode($_POST['VAR_VALUE']);
	$strFilePath = urljsdecode($_POST['FILE_RELATIVE_PATH']);
	
	if ( !($oPage = getFileObjectAndFind(SITE_PATH.$strFilePath)) )
		return setError('Can not find the page '.$oPage->getMenuName());
	
	if(!$oPage->oPConfigFile->setParam($strVarName, $strVarValue))
		return false;	
		
	if(!$oPage->oPConfigFile->Save())
		return false;

		
	return true;
}

function movefile(){
	if( !isset($_POST['FILE_RELATIVE_PATH']) )
		return setError('Internal error in movefile, FILE_RELATIVE_PATH is not set');
	if( !isset($_POST['TARGET_DIR']) )
		return setError('Internal error in movefile, TARGET_DIR is not set');

	if( !$oFileToMove = &getFileObjectAndFind(SITE_PATH.urljsdecode($_POST['FILE_RELATIVE_PATH'])) )
		return setError('Internal error in movefile, can not find file to move');
	if( !$oDirTarget  = &getFileObjectAndFind(SITE_PATH.urljsdecode($_POST['TARGET_DIR'])) )
		return setError('Internal error in movefile, can not find file target dir');
	
	if( !$oFileToMove->Move($oDirTarget->path) )
		return false;
	
	return true;
}

function resizeimage(){
	require(SITE_PATH.'core/lib/pimage.php');
	if( !isset($_POST['FILE_RELATIVE_PATH']) )
		return setError('Internal error in resize image, FILE_RELATIVE_PATH is not set');
	if( !isset($_POST['NEW_SIZE']) )
		return setError('Internal error in resize image, NEW_SIZE is not set');
	
	$iNewSize = intval($_POST['NEW_SIZE']);
	if($iNewSize < 100 || $iNewSize > 2048){
		return setError(_('Size value is not valid.'));
	}
	$oImage = new PImage(SITE_PATH.urljsdecode($_POST['FILE_RELATIVE_PATH']));
	if( !is_file($oImage->path) )
		return setError('Internal error in resize image, object image not found.');

	if( !$oImage->is_image() )
		return setError('The file is not an image');

	if( !$oImage->ResizeMax($iNewSize) )
		return false;
	
	return true;
}

/**
 * Function sortfiles
 * This fonction is call by the sortfile javascript plugins.
 * It renames the files. The file begin with a number are ordered.
 * 
 * If an object file not begin with a number, it is not ordered. 
 * 
 * @return: true if suceed, else return false.
 */
function sortfiles($tabFilesNew){
	//if less than two files no need to sort
	if(sizeof($tabFilesNew) < 2) return;
	
	//get the dir to order, take the second element because in some cas the first element is ../
	$pTemp = new PFile(SITE_PATH.urljsdecode($tabFilesNew[1]));
	$oDirToOrder = new PDir($pTemp->getParentPath());

	$i=0;
	foreach($tabFilesNew as $strFile){
		$strFile = urljsdecode($strFile);
		//if not parent file
		if(SITE_PATH.$strFile != $oDirToOrder->getParentPath()){
			//get the file number, if number exist reorder it
			//this is the original file number, if modified twice it change in php but not in html
			$oFileTest = new PFile($oDirToOrder->path.SLASH.basename($strFile));		
			if( $oFileTest->is_page() || $oFileTest->is_dircategory() || $oFileTest->is_link() ){
				$oFile =  getFileObject($oFileTest->path);	
				$iCurrOrder = $oFile->getMenuOrder();
				$i++;
				if( $iCurrOrder !=  $i){
					//print('reorder '.$oFile->getName().' from '.$iCurrOrder.' to '.$i);
					if(!$oFile->setMenuOrder($i)){
						return false;
					}
				}//if file order has changed
			}//end if filenumber is set
		}//end if ofile not parent dir
	}//end foreach
	return true;
}

function ajaxGetText($strText){
	echo _($strText);
}

function loadHistoryPage($strPath) {
	// recup content html
	$oPpage = new PPage($strPath);
	$fckContent = $oPpage->getEditorFileContent();
	echo $fckContent;
}

function clearCache($strType='site'){

	$return = pcms_clearcache($strType);
	if(!$return){
		printError();
		return false;
	}
	echo _('cache is now empty');
	return true;
}


?>