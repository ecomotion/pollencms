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
			<div class="block-icon"><div class="img-icon"><img src="<?=SITE_URL?>core/admin/theme/images/admin/icon-pages.gif" /></div><div class="title-icon"><?php echo _('Pages'); ?></div><div class="reset"></div></div>
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
		
<!--		<a href="admin_file_editor.php?file=<?=urlencode("site/config/config.ini")?>" class="panel-link infobulles" id="rss">
			<img src="<?=SITE_URL?>core/admin/theme/images/admin/icon-rss.jpg" />
			<span>
				<h3>Gestion des news rss</h3>
				<p>Cette section vous permet de modifier l'agrégateur de news.</p>
			</span>
		</a>
		<a href="admin_file_management.php?current_dir=<?=urlencode(PAGES."/fr/Actualites/Agenda")?>" class="panel-link infobulles" id="agenda">
			<img src="<?=SITE_URL?>core/admin/theme/images/admin/icon-agenda.jpg" />
			<span>
				<h3>Gestion de l'agenda</h3>
				<p>Dans ce module vous pouvez gérer l'agenda du site.<br /> Un fichier = une rubrique</p>
			</span>
		</a>
		<a href="admin_file_management.php?current_dir=<?=urlencode(PAGES."/fr/Actualites/Actualites")?>" class="panel-link infobulles" id="news" style="margin-right:0px">
			<img src="<?=SITE_URL?>core/admin/theme/images/admin/icon-news.jpg" />
			<span>
				<h3>Gestion des Actualités</h3>
				<p>Dans ce module vous pouvez gérer l'actualité du site.<br />Un fichier = Une Actualité</p>
			</span>
		</a>
-->	
	<div class="reset"></div>
	
	</tr></table>
	</div>
<div class="reset"></div>

<?php
}//end connect ok
include 'admin_bottom.php';
?>
