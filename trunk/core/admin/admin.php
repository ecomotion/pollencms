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
		<a href="admin_file_management.php?current_dir=<?php echo urlencode(POFile::getPathRelativePath(PAGES_PATH));?>"  class="panel-link infobulles" id="pages">
			<img src="<?=SITE_URL?>core/admin/theme/images/admin/icon-pages.jpg" />
			<span>
				<h3> Gestion des pages </h3>
				<p>Dans cette espace vous pouvez gérer la structure et les pages de votre site.</p>
			</span>
		</a>
		<a href="admin_file_management.php?current_dir=<?php echo urlencode(POFile::getPathRelativePath(MEDIAS_PATH));?>" class="panel-link infobulles" id="images">
			<img src="<?=SITE_URL?>core/admin/theme/images/admin/icon-images.jpg" />
			<span>
				<h3>Gestion de vos médias</h3>
				<p>Vous pouvez gérer ici les images du site ainsi que les fichiers joins (pdf ....)</p>
			</span>
		</a>
		<?php if(isSuperAdmin()){?>
		<a href="admin_configurator.php" class="panel-link infobulles" id="options">
			<img src="<?=SITE_URL?>core/admin/theme/images/admin/icon-options.jpg" />
			<span>
				<h3><?php echo _('Configuration du site');?></h3>
				<p>Vous pouvez ici gérer la configuration de votre site</p>
			</span>
		</a>
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
-->	</div>
<div class="reset"></div>

<?php
}//end connect ok
include 'admin_bottom.php';
?>
