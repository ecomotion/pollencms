<?php
include('admin_top.php');

if( isConnected() && !isset($_GET['ajax']) ){
	echo '
		<script language="JavaScript">
			$(function(){
				if(window.top != window)
					window.top.oDialogAdmin.dialog("fullscreen",true);
			});
		</script>
	';
}

require "../config.inc.php";
require SITE_PATH.'core/lib/pdir.php';
require SITE_PATH.'core/lib/pimage.php';
require SITE_PATH.'core/lib/ppage.php';
require SITE_PATH.'core/lib/lib_functions.php';

define("EDIT_FILE",true);

/*Security, can't browse .. directory*/
if(!isset($_GET["file"]) || $_GET["file"]== '' || eregi("\.\.",$_GET["file"])) {
	printFatalError(_('You should specified a file to edit'));
}

$pFileTemp = &getFileObject(SITE_PATH.urldecode($_GET["file"]));
$pfile=&getFileObjectAndFind($pFileTemp->path,'file');
if($pfile === false){
	if($pFileTemp->is_configfile()){
		//must create it
		$pfile=&$pFileTemp;;
	}else if( preg_match('/index\.htm[l]?$/',basename($pFileTemp->path))!==false ){
		//if index file create it
		$pfile = &$pFileTemp;
	} else
		printFatalError();
}

//url back
$pdirparent = &getFileObject($pfile->getParentPath());

if( isConnected() ){
	echo '
		<div id="path">
		<div id="imgHome"></div>
	';

	$strPath = $pfile->path;
	$oRoot = new PDir( (strstr($pfile->path,PAGES_PATH)!==false)?PAGES_PATH:((strpos($pfile->path,PAGES_MODELS_PATH)!==false)?PAGES_MODELS_PATH:SITE_PATH));
	
	$tabGuid=array();
	while( $oRoot->path != $strPath  && strlen($strPath)>1 ){
		$o = &getFileObject($strPath);
		if(is_dir($o->path)){
			$fileMangementUrl='admin_file_management.php?rootpath='.rawurlencode(str_replace(SITE_PATH,'',PAGES_PATH)).'&current_dir='.rawurlencode($o->getRelativePath(PAGES_PATH));
			$tabGuid[]=array('NAME'=>$o->getName(),'URL'=>$fileMangementUrl);
		}
		$strPath=$o->getParentPath();
	}
	$tabGuid[]=array(
		'NAME'=>str_replace('languages',_('Site Pages'),$oRoot->getName()),
		'URL'=>'admin_file_management.php?rootpath='.rawurlencode(str_replace(SITE_PATH,'',$oRoot->path))
	);
	$tabGuid = array_reverse($tabGuid);
	foreach($tabGuid as $strUrlGuid){
		echo '<a href="'.$strUrlGuid['URL'].'">'.$strUrlGuid['NAME'].'</a> > ';
	}
	echo $pfile->getShortName();

	echo '
		</div>';

	echo $pfile->DisplayEditor();
}//end user connected
include("admin_bottom.php");
?>
