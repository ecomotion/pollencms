<?php
include '../config.inc.php';
require SITE_PATH.'core/lib/localization.php';
require SITE_PATH.'core/lib/pollencms.php';
require SITE_PATH.'core/lib/ptextfile.php';
require SITE_PATH.'core/lib/ppage.php';
require SITE_PATH.'core/lib/pdir.php';
require SITE_PATH.'core/lib/pplugindir.php';
require SITE_PATH.'core/lib/lib_error.php';
require SITE_PATH.'core/lib/lib_functions.php';
require(SITE_PATH.'vendors/jscripts/fckeditor/fckeditor.php');

if(!isset($_SESSION)){
	session_start();
}

if( !isConnected() && !stristr($_SERVER['PHP_SELF'],'admin.php')){
	header('location:'.SITE_URL.'core/admin/admin.php?redirect='.urlencode($_SERVER['REQUEST_URI']));
}


if(isset($_POST["todo"]) && $_POST["todo"]=="connect"){
	if( msConnect(trim($_POST['uLogin']), trim($_POST['uPass']), $configFile) === true ){
		header("location:".((isset($_GET['redirect']))?urldecode($_GET['redirect']):$_SERVER["REQUEST_URI"]));
	}else{
		$bErrorConnection=true;
	}
}
if(isset($_GET["todo"]) && $_GET["todo"]=="disconnect"){
	msDisconnect();
}


if(!isset($_GET["ajax"])){
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex, nofollow">

<title>ADMINISTRATION</title>
<link rel="icon" type="image/png" href="<?php echo THEME_DIR; ?>/images/favicon.jpg" />

<script language="JavaScript">
	//<?php echo 'var session_id="'.session_id().'"'; ?>
</script>


<script language="JavaScript" src="<?php echo SITE_URL;?>vendors/jscripts/jquery.js" ></script>

<script type="text/javascript" src="<?php echo SITE_URL;?>vendors/jscripts/jqueryui/jquery-ui-1.7.1.js"></script> 

<script language="JavaScript" src="<?php echo SITE_URL;?>vendors/jscripts/swfupload/swfupload.js" ></script>
<script language="JavaScript" src="<?php echo SITE_URL;?>core/jscripts/admin_swfupload_handlers.js" ></script>

<script type="text/javascript" src="<?php echo SITE_URL;?>vendors/jscripts/jqueryplugins/jquery.progression.js"></script>
<script language="JavaScript" src="<?php echo SITE_URL;?>vendors/jscripts/jqueryplugins/jquery.contextmenu.js" ></script>
<script type="text/javascript" src="<?php echo SITE_URL;?>vendors/jscripts/jqueryplugins/jquery.hotkeys.js" ></script>
<script type="text/javascript" src="<?php echo SITE_URL;?>vendors/jscripts/jqueryplugins/jquery.ifixpng.js" ></script>
<script type="text/javascript" src="<?php echo SITE_URL;?>vendors/jscripts/jqueryplugins/jquery.shadow.js" ></script>
<script type="text/javascript" src="<?php echo SITE_URL;?>vendors/jscripts/jqueryplugins/jquery.fancyzoom.js" ></script>
<script type="text/javascript" src="<?php echo SITE_URL;?>vendors/jscripts/jqueryplugins/jquery.jeditable.js" ></script>
<script type="text/javascript" src="<?php echo SITE_URL;?>vendors/jscripts/jqueryplugins/jgrowl/jquery.jgrowl-1.1.2_compressed.js" ></script>
<link   rel="stylesheet"      href="<?php echo SITE_URL;?>vendors/jscripts/jqueryplugins/jgrowl/jquery.jgrowl.css" />

<script language="JavaScript" src="<?php echo SITE_URL;?>?page=config.js" ></script>
<script language="JavaScript" src="<?php echo SITE_URL;?>core/jscripts/functions.js" ></script>
<script language="JavaScript" src="<?php echo SITE_URL;?>core/jscripts/admin_page.js" ></script>
<script language="JavaScript" src="<?php echo SITE_URL;?>core/jscripts/admin_file_management.js" ></script>
<script language="JavaScript" src="<?php echo SITE_URL;?>core/jscripts/admin_file_editor.js" ></script>
<script language="JavaScript" src="<?php echo SITE_URL;?>core/jscripts/admin_configurator.js" ></script>

<?
	if(!doEventAction('adminHeader',array()))
		printError();
	
?>


<link rel="stylesheet" href="<?php echo SITE_URL;?>core/admin/theme/css/admin.css" />
<link rel="stylesheet" href="<?php echo SITE_URL;?>vendors/jscripts/jqueryui/themes/smoothness/jquery-ui-1.7.1.css" />
<script language="JavaScript">
	window.onload = function(){if(window.parent && window.parent.oDialogAdmin){
		window.parent.oDialogAdmin.dialog("option","title","Panneau d'Administration");
	}};
</script>
</head>
<body>

<?php
if(isConnected() ){
?>

<a href="#" id="btnDeconnecter" class="btnDeconnecterEnable" onclick="return confirmDisconnect();"></a>
<form id="formAdminAll">
<a href="#" id="btnBack"	class="btnNav btnBackOff"	onClick="myGoBack();return false;"		style="float:left"></a>
<a href="#" id="btnFow"		class="btnNav btnForwOff"	onClick="myGoForward();return false;"	style="float:left"></a>
<a href="#" id="btnToutAfficher" style="float:left"></a>

</form>
<div class="reset"></div>
<div id="contentAdmin">
<?php }else { 
?>
<div id="contentAdmin">
<form action="<?php echo ($_SERVER["PHP_SELF"].(isset($_GET["redirect"])?'?redirect='.$_GET['redirect']:''));?>" method="post" id="formConnect" style="margin-bottom:40px;">
	<fieldset>
		<legend>Connection</legend>
		<br />
		<label><?php echo _('Login');?>:</label> <input type="text" name="uLogin" class="input-text" /><br />
		<label><?php echo _('Password');?>:</label><input type="password" name="uPass" class="input-text"  /><br />
		<?php 
		if(isset($bErrorConnection) && $bErrorConnection)
			echo '<script language="JavaScript">
				$(function(){
					msgBoxError("'.getError().'",10);
				});			
			</script>';
			
		?>

		<input type="hidden" name="todo" value="connect" /><br />
	</fieldset>
	<div style="text-align:right">
		<input type="button" class="pcmButton" onClick="if(window.top.oDialogAdmin) window.top.oDialogAdmin.dialog('close');return false;" value="<?php echo _('cancel');?>" /> 
		<input type="submit" value="<?php echo _('connection');?>" class="pcmButton"/>
	</div>
</form>
<div class="reset"></div>
<?php } //end not connected print form
	}//end if isset ajax 
?>

