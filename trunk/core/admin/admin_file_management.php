<?php
/* security issue, if user is not connected go to admin page */
session_start();
require '../lib/lib_functions.php';
if(!isConnected()){
	header("Location:admin.php");
}

include "../config.inc.php";
require SITE_PATH.'core/lib/pdir.php';
require SITE_PATH.'core/lib/pimage.php';
require SITE_PATH.'core/lib/ppage.php';
require SITE_PATH.'core/lib/plink.php';



include("admin_top.php");
define("EDIT_FILE",true);
?>
<script language='JavaScript'>
	$(function(){
		//on repasse la fenêtre en petit si on est pas en mode ajax
		if(window.top != window){
			window.top.oDialogAdmin.dialog('fullscreen',false);
		}
	});
</script>

<?php
/*Security, can't browse .. directory*/
if( !isset($_GET["rootpath"])  || $_GET["rootpath"] == '' || preg_match("/\.\./",urldecode($_GET["rootpath"])) ){
	if( isset($_GET["current_dir"])  && $_GET["current_dir"] != '' && !preg_match("/\.\./",urldecode($_GET["current_dir"])) ){
		$rootpath=SITE_PATH.urldecode($_GET["current_dir"]);
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
	$current_dir=$rootpath.SLASH.urldecode($_GET["current_dir"]);
}
$pcurrent_dir = &getFileObjectAndFind($current_dir);
$proot_dir = new PDir($rootpath);

//NOW WE HAVE OUR ROOT DIR AND CURR DIR
if(!is_dir($rootpath)) die('root path not exists.');
if(!$pcurrent_dir)  die('current directory not found');
?>

<!-- FILE PATH -->
<div id="path" style="margin-top:40px;">
	<div style='float:right;margin-top:-10px'>
		<a  id="btnNewFolderSmall" class="btnNewSmall" href="javascript:createDir('<?php echo urljsencode($pcurrent_dir->getRelativePath()); ?>');"  title="<?php echo _('create a directory')?>"></a>
		<?php if(strpos($proot_dir->path,PAGES_PATH)!== FALSE){?>
		<a id="btnNewFileSmall"   class="btnNewSmall" href="javascript:createFile('<?php echo urljsencode($pcurrent_dir->getRelativePath()); ?>');" title="<?php echo _('create a page')?>"></a>
		<?php } ?>
		<?php if(strpos($proot_dir->path,MEDIAS_PATH)!== FALSE){?>
			<a  href="javascript:clickSWFUpload('<?php echo session_id().'\', \''.urljsencode($pcurrent_dir->getRelativePath()); ?>');" class="btnNewSmall" id="btnUploadFileSmall" title="<?php echo _('upload file') ?>"></a>
		<?php } ?>
	</div>

<div id="imgHome"></div><a href="<? echo $_SERVER["PHP_SELF"]."?rootpath=".urlencode($proot_dir->getRelativePath());?>">
<?php echo (strstr($proot_dir->path,PAGES_PATH)!==false)?_('Site Pages'):$proot_dir->getPrintedName();?>
</a><?php echo $pcurrent_dir->getLinkPath($rootpath); ?>
</div><!--end divpath -->

<div id="browser" class="sortable">
<div id="menu_browser" class="contextMenu">
	<ul>
		<li><a id="newdir" href="javascript:createDir('<?php echo urljsencode($pcurrent_dir->getRelativePath()); ?>');"  title="<?php echo _('create a directory')?>"><?php echo _('create a directory')?></a></li>
		<?php if(strpos($proot_dir->path,PAGES_PATH)!== FALSE){?>
		<li><a id="newfile" href="javascript:createFile('<?php echo urljsencode($pcurrent_dir->getRelativePath()); ?>');" title="<?php echo _('create a page')?>"><?php echo _('create a page')?></a></li>
		<?php } ?>
		<?php if(strpos($proot_dir->path,MEDIAS_PATH)!== FALSE){?>
		<li><a href="javascript:clickSWFUpload('<?php echo session_id().'\', \''.urljsencode($pcurrent_dir->getRelativePath()); ?>');" id="uploadfile" title="<?php echo _('upload file') ?>"><?php echo _('upload file') ?></a></li>
		<?php } ?>
	</ul>
</div>

<?php

// parrent Dir, only print if not on root directory
if($current_dir != SITE_PATH && $current_dir!=$rootpath && $current_dir ){
	$pdirParent = $pcurrent_dir->getParent();
	$pdirParent->Display(70,'../',$url=false,$proot_dir);
}

//On affice les fichiers
$listDir = $pcurrent_dir->listDir();
foreach($listDir as $file){
	if(is_dir($file)){
		if( $obj =& getFileObject($file) )
			$obj->Display(70,$print=false,$url=false,$proot_dir);
	} else {
		if( $obj =& getFileObject($file) )
			$obj->Display(70,$url=false,$proot_dir);
	}	
}
?>
<div class="reset"></div>
</div><!--end div browser -->

<?php
	include("admin_bottom.php");
?>
