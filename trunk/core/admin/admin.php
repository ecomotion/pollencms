<?php
session_start();
require '../config.inc.php';
require SITE_PATH.'core/lib/lib_error.php';
require SITE_PATH.'core/lib/lib_functions.php';
require SITE_PATH.'core/lib/peventsmanager.php';

include 'admin_top.php';

if( isConnected() ){

?>

	<div id="panel-links">
	<table align="center" border="0">
	<tr><td>
		<a href="admin_file_management.php?current_dir=<?php echo urlencode(POFile::getPathRelativePath(PAGES_PATH));?>"  class="panel-link infobulles" id="pages">
			<div class="block-icon"><div class="img-icon"><img src="<?=SITE_URL?>core/admin/theme/images/admin/icon-pages.gif" /></div><div class="title-icon"><?php echo _('Very long title Pages'); ?></div><div class="reset"></div></div>
			<span>
				<h3> Gestion des pages </h3>
				<p>Dans cette espace vous pouvez gérer la structure et les pages de votre site.</p>
			</span>
		</a>
	</td><td>
		<a href="admin_file_management.php?current_dir=<?php echo urlencode(POFile::getPathRelativePath(PAGES_MODELS_PATH));?>"  class="panel-link infobulles" id="pages">
			<div class="block-icon"><div class="img-icon"><img src="<?=SITE_URL?>core/admin/theme/images/admin/icon-pages.gif" /></div><div class="title-icon"><?php echo _('Models'); ?></div><div class="reset"></div></div>
			<span>
				<h3> Gestion des modèles </h3>
				<p>Dans cette espace vous pouvez gérer les modèles de pages de votre site.</p>
			</span>
		</a>
	</td><td>
		<a href="admin_file_management.php?current_dir=<?php echo urlencode(POFile::getPathRelativePath(MEDIAS_PATH));?>" class="panel-link infobulles" id="images">
			<div class="block-icon"><div class="img-icon"><img style="padding-top:5px;" src="<?=SITE_URL?>core/admin/theme/images/admin/icon-images.gif" /></div><div class="title-icon"><?php echo _('Medias'); ?></div><div class="reset"></div></div>
			<span>
				<h3>Gestion de vos médias</h3>
				<p>Vous pouvez gérer ici les images du site ainsi que les fichiers joins (pdf ....)</p>
			</span>
		</a>
	</td>
		<?php if(isSuperAdmin()){?>
		<td>
		<a href="admin_configurator.php" class="panel-link infobulles" id="options">
			<div class="block-icon"><div class="img-icon"><img style="padding-top:10px;" src="<?=SITE_URL?>core/admin/theme/images/admin/icon-options.gif" /></div><div class="title-icon"><?php echo _('Options'); ?></div><div class="reset"></div></div>
			<span>
				<h3><?php echo _('Configuration du site');?></h3>
				<p>Vous pouvez ici gérer la configuration de votre site</p>
			</span>
		</a>
		</td>
		<?php }
		
		if(!doEventAction('adminmainmenu',array()))
			printError();
		?>
		
	<div class="reset"></div>
	
	</tr></table>
	</div>
<div class="reset"></div>

<?php
}//end connect ok
include 'admin_bottom.php';
?>
