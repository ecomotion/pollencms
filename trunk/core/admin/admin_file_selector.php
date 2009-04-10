<?php
include "../config.inc.php";
require SITE_PATH.'core/lib/lib_functions.php';
require SITE_PATH.'core/lib/localization.php';
define ("FILE_SELECTOR","true");
require SITE_PATH.'core/lib/pdir.php';
require SITE_PATH.'core/lib/pimage.php';
require SITE_PATH.'core/lib/plink.php';

if(!isConnected()) header("Location:admin.php");

/*Security, can't browse .. directory*/
/*Security, can't browse .. directory*/

//Add by mathieu for Ghyslain, record in memory the last directory position
if(!isset($_GET["current_dir"]) && isset($_SESSION['LAST_ROOTPATH']) && isset($_GET["rootpath"]) && $_SESSION['LAST_ROOTPATH'] == $_GET["rootpath"])
	$_GET["current_dir"] = $_SESSION['LAST_CURRENT_DIR'];
$_SESSION['LAST_ROOTPATH']=isset($_GET["rootpath"])?$_GET["rootpath"]:'';
$_SESSION['LAST_CURRENT_DIR']=isset($_GET["current_dir"])?$_GET["current_dir"]:'';
	
if( !isset($_GET["rootpath"])  || $_GET["rootpath"] == '' || preg_match("/\.\./",urldecode($_GET["rootpath"])) ){
	if( isset($_GET["current_dir"])  && $_GET["current_dir"] != '' && !preg_match("/\.\./",urldecode($_GET["current_dir"])) ){
		$rootpath=SITE_PATH.urljsdecode($_GET["current_dir"]);
		$_GET["current_dir"]='';
	}else{
		$rootpath=SITE_PATH;
	}
}else {
	$rootpath=SITE_PATH.(isset($_GET["rootpath"])?urldecode($_GET["rootpath"]):'');
}
if( !isset($_GET["current_dir"])  || $_GET["current_dir"] == '' || preg_match("/\.\./",urldecode($_GET["current_dir"])) ){
		$current_dir = $rootpath;
}else {
	$current_dir=$rootpath.SLASH.urljsdecode($_GET["current_dir"]);
}

$pcurrent_dir = &getFileObject($current_dir);
$proot_dir = new PDir($rootpath);

if(!is_dir($rootpath)) die('root path not exists.');
if(!$pcurrent_dir)  die('current directory not found');



$rootpathdir = $proot_dir->getRelativePath();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex, nofollow" />

<link rel="stylesheet" href="<?php echo SITE_URL;?>core/admin/theme/css/admin.css" />
<?php
	$strUiTheme = $configFile->getDirectParam('UI_THEME');
	echo '<link rel="stylesheet" href="'.SITE_URL.'vendors/jscripts/jqueryui/themes/'.$strUiTheme.'/jquery-ui-1.7.1.css" />';
?>
<style>
</style>

<script type="text/javascript" src="<?=SITE_URL?>vendors/jscripts/jquery.js"></script>
<script type="text/javascript" src="<?=SITE_URL?>index.php?page=config.js"></script>

<script language="JavaScript" src="../jscripts/functions.js"></script>
<script type="text/javascript" src="<?=SITE_URL?>vendors/jscripts/jqueryui/jquery-ui-1.7.1.js"></script> 
<script language="JavaScript" src="<?=SITE_URL?>vendors/jscripts/swfupload/swfupload.js" ></script>
<script language="JavaScript" src="<?=SITE_URL?>core/jscripts/admin_swfupload_handlers.js" ></script>
<script type="text/javascript" src="<?=SITE_URL?>vendors/jscripts/jqueryplugins/jquery.progression.js"></script>
<script language="JavaScript" src="<?=SITE_URL?>vendors/jscripts/jqueryplugins/jquery.contextmenu.js" ></script>
<script type="text/javascript" src="<?=SITE_URL?>vendors/jscripts/jqueryplugins/jquery.jeditable.js" ></script>
<script language="JavaScript" src="../jscripts/admin_file_management.js"></script>

<script language="JavaScript">
function myRelodPage(){
	window.location = '<?php echo $_SERVER['REQUEST_URI']; ?>';
}
function setUrl(url){
	<?php
		$rootpathdirurl = $rootpathdir;
		$uploadpathurl = POFile::getPathRelativePath(MEDIAS_PATH);
		if(SLASH != '/'){
			$rootpathdirurl = str_replace(SLASH,'/',$rootpathdirurl);
			$uploadpathurl = str_replace(SLASH,'/',$uploadpathurl);
		}
		echo '
			var rootpathdirurl = "'.$rootpathdirurl.'";
			var uploadpathurl = "'.$uploadpathurl.'";
		';
	?>
	//file selector
	if(oSelec = window.opener.document.getElementById("cmbLinkProtocol")){
		oSelec.selectedIndex=4;//set protocol on other

		if( uploadpathurl == rootpathdirurl )
			window.opener.SetUrl("{#SITE_URL#}"+rootpathdirurl+"/"+encodeURI(url));
		else {//user select a page of the site
			if(!(/\.[a-z]{1,}$/gi.test(url)))
				url+='/';
			window.opener.SetUrl("{#SITE_URL#}"+encodeURI(url.replace(/[0-9]*_/gi,'').replace(/\s/gi,'-').toLowerCase()));
		}
	//select an image
	}else if(window.opener.document.getElementById("txtUrl")){
		window.opener.SetUrl("<?=SITE_URL?>"+rootpathdirurl+"/"+encodeURI(url));
	}
//selection d'un fichier lors du move
	else{
		window.opener.SetUrl(rootpathdirurl+"/"+encodeURI(url));
	}
	window.close();
	return false;
}
function setUrlFileChoice(strFilename){
	$("#path_selected").val(strFilename.replace(/[0-9]*_/gi,''));
	$("#real_path_selected").val(strFilename);
	setUrl(strFilename);
}
function cancel(){
	window.close();
}
</script>
</head>
<body class="pollenadmin">
<div id="contentAdmin">
<h2><?php echo _('Links');?></h2>
<div id="links">
	<div class="folderLinks">
		<?php 
		$oPdirPages = &getFileObject(PAGES_PATH);
		echo $oPdirPages->Display(70,_('Site Pages'));
		
		$oPdirUpload = &getFileObject(MEDIAS_PATH);
		echo $oPdirUpload->Display(70,_('Media'));
		?>
	</div>
</div><!-- end div links -->
<div class="reset" style="margin-top:130px"></div>


<h2><?php echo _('Choose a file');?></h2>
<div id="path">
	<div style='float:right;margin-top:-10px'>
		<a href="#" id="btnNewFolderSmall" class="btnNewSmall" onclick="javascript:createDir('<?php echo urlencode($pcurrent_dir->getRelativePath()); ?>');return false;"  title="<?php echo _('create a directory')?>"></a>
		<?php if(strpos($proot_dir->path,MEDIAS_PATH)!== FALSE){?>
			<a href="#" onclick="javascript:clickSWFUpload('<?php echo session_id().'\', \''.urlencode($pcurrent_dir->getRelativePath()); ?>',this);return false;" class="btnNewSmall" id="btnUploadFileSmall" title="<?php echo _('upload file') ?>"></a>
		<?php } ?>
	</div>
	<div id="imgHome"></div><a href="<?=$_SERVER["PHP_SELF"]."?current_dir=&rootpath=".urlencode($proot_dir->getRelativePath())?>"><?=_('home')?> </a><?php echo $pcurrent_dir->getLinkPath($rootpath); ?>
	
</div>
<div id="browser" style="height:290px;">
<div id="menu_browser" class="contextMenu">
	<ul>
		<li><a href="#" id="newdir" onClick="javascript:createDir('<?php echo urlencode($pcurrent_dir->getRelativePath()); ?>');return false;"  title="<?php echo _('create a directory')?>"><?php echo _('create a directory')?></a></li>
		<?php if(strpos($proot_dir->path,MEDIAS_PATH)!== FALSE){?>
		<li><a href="#" id="uploadfile" onclick="javascript:clickSWFUpload('<?php echo session_id().'\', \''.urlencode($pcurrent_dir->getRelativePath()); ?>',this);return false;" title="<?php echo _('upload file') ?>"><?php echo _('upload file') ?></a></li>
		<?php } ?>
	</ul>
</div>

<?php
/**
List Files
*/
// parrent Dir, only print if not on root directory
if($current_dir != SITE_PATH && $current_dir!=$rootpath && $current_dir ){
	$pdirParent = $pcurrent_dir->getParent();
	echo $pdirParent->Display(100,"../",$url=false,$proot_dir);
}

//Faire la liste des répertoires
$listDir = $pcurrent_dir->listDir($pcurrent_dir->ONLY_DIR,true);
foreach($listDir as $dir){
	$objDir = &getFileObject($dir);
	echo $objDir->Display(70,$print=false,$url=false,$proot_dir);
}

/* files list */
$listFiles = $pcurrent_dir->listDir($pcurrent_dir->ONLY_FILES,true,".*","\.ini$");
foreach($listFiles as $file){
	$objFile = &getFileObject($file);
	$strRelativeUrl = $objFile->getRelativePath($proot_dir->path);
	if(SLASH != '/' ) $strRelativeUrl = str_replace(SLASH,'/',$strRelativeUrl);
	$url='"javascript:setUrlFileChoice(\''.$strRelativeUrl.'\')"';
	//if(eregi(TEXTEDIT_WYSWYG."|\.php",$file)){$url="\"javascript:setUrl('".urlencode(ereg_replace("[0-9]*_","",substr($objFile->getRelativePath(),strlen($rootpath)+1)))."')\"";}
	
	echo $objFile->Display(70,$url,$proot_dir);
	
}

?>
</div>

<form onSubmit="javascript: return setUrl(this.elements['real_path_selected'].value);" id="form_select_path" >

	<input type="text" id="path_selected" value="<?php echo preg_replace('/[0-9]*_/','',$pcurrent_dir->getRelativePath($rootpath));?>" />
	<input type="hidden" id="real_path_selected" value="<?php echo $pcurrent_dir->getRelativePath($rootpath);?>" />

	<button type="submit" class="ui-state-default ui-corner-all"><?php echo _('Select'); ?></button>
	<button type="button" class="ui-state-default ui-corner-all" onClick="cancel();return false;"><?php echo _('Cancel'); ?></button>

</form>

</div>
</body>
</html>
