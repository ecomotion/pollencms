<?php
include("admin_top.php");
?>

<?php
if( isConnected() && !isset($_GET['ajax']) ){
?>
<script language='JavaScript'>
	$(function(){
		if(window.top != window)
			window.top.oDialogAdmin.dialog('fullscreen',true);
	});
</script>
<?php
}

require "../config.inc.php";
require SITE_PATH.'core/lib/pdir.php';
require SITE_PATH.'core/lib/pimage.php';
require SITE_PATH.'core/lib/ppage.php';
require SITE_PATH.'core/lib/lib_functions.php';

define("EDIT_FILE",true);

/*Security, can't browse .. directory*/
if(!isset($_GET["file"]) || $_GET["file"]=="" || eregi("\.\.",$_GET["file"])) {
	printFatalError(_("You should specified a file to edit"));
}
else{
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
}

if( isConnected() ){
	
?>
<div id="path">
<div id="imgHome"></div>
<?php
	//ghyslain request, add the path to the file
	$strPath = $pfile->path;
	$tabGuid=array();
	while(PAGES_PATH != $strPath  && strlen($strPath)>1){
		$o = &getFileObject($strPath);
		if(is_dir($o->path)){
			$fileMangementUrl='admin_file_management.php?rootpath='.rawurlencode(str_replace(SITE_PATH,'',PAGES_PATH)).'&current_dir='.rawurlencode($o->getRelativePath(PAGES_PATH));
			$tabGuid[]=array('NAME'=> preg_replace('/^[0-9]*_/','',$o->getName()),'URL'=>$fileMangementUrl);
		}
		$strPath=$o->getParentPath();
	}
	$tabGuid[]=array('NAME'=>_('home'),'URL'=>'admin_file_management.php?rootpath='.rawurlencode(str_replace(SITE_PATH,'',PAGES_PATH)));
	$tabGuid = array_reverse($tabGuid);
	foreach($tabGuid as $strUrlGuid)
		echo '<a href="'.$strUrlGuid['URL'].'">'.$strUrlGuid['NAME'].'</a> > ';

?>
<?=$pfile->getShortName();?>
</div>
<?php
if(eregi(TEXTEDIT_WYSWYG,$pfile->getname())){
	$strFCKConfigDir = SITE.SLASH.'config'.SLASH.'fckeditor'.SLASH;
	$strFCKConfigUrl = SITE_URL.((SLASH=='/')?$strFCKConfigDir:str_replace(SLASH,'/',$strFCKConfigDir)); 
	$strFCKConfigDir = CONFIG_DIR.'fckeditor'.SLASH;
	
	$oFCKeditor = new FCKeditor('text') ;
	$oFCKeditor->BasePath = SITE_URL.'vendors/jscripts/fckeditor/';
	$oFCKeditor->Value = $pfile->getEditorFileContent();
	$oFCKeditor->Height = '400' ;
	
	//the styles to apply to the editor, the page style and the fckeditor.css style
	$fileStyleUrls='';
	if($pageStyle = $pfile->getConfig("PAGE_STYLE")) $fileStyleUrls .= THEME_URL.'css/'.$pageStyle.',';
	if(is_file($strFCKConfigDir.'fckeditor.css')) $fileStyleUrls .= $strFCKConfigUrl.'fckeditor.css';
	$oFCKeditor->Config["EditorAreaCSS"] =$fileStyleUrls;
	
	$oFCKeditor->Config["ImageBrowserURL"] = SITE_URL."core/admin/admin_file_selector.php?rootpath=".urlencode(POFile::getPathRelativePath(MEDIAS_PATH));
	$oFCKeditor->Config["FlashBrowserURL"] = SITE_URL."core/admin/admin_file_selector.php?rootpath=".urlencode(POFile::getPathRelativePath(MEDIAS_PATH));
	//go to the parent path
	$oFCKeditor->Config["LinkBrowserURL"] = SITE_URL."core/admin/admin_file_selector.php?current_dir=".urlencode(POFile::getPathRelativePath($pfile->getParentPath(), PAGES_PATH))."&rootpath=".urlencode(POFile::getPathRelativePath(PAGES_PATH));
	$oFCKeditor->Config["CustomConfigurationsPath"] = SITE_URL.'?page=fckconfig.js';
	
	if(is_file($strFCKConfigDir.'fcktemplates.xml'))
		$oFCKeditor->Config["TemplatesXmlPath"]	= SITE_URL.'?page=fcktemplates.xml' ;
	if(is_file($strFCKConfigDir.'fckstyles.xml'))
		$oFCKeditor->Config["StylesXmlPath"]	= SITE_URL.'?page=fckstyles.xml' ;
	
	
	$strBarName = getUserEditorBar();
	if($strBarName) $oFCKeditor->ToolbarSet = $strBarName;
	

	$strTabsTpl ='
	<div id="tabPageEditor">
		<ul>
			<li class="ui-tabs-nav-item"><a href="#fragEditor"><span>Edition</span></a></li>
			<li class="ui-tabs-nav-item"><a href="#fragOptions"><span>Options</span></a></li>
		</ul>
		
		<div id="fragEditor">{TAB_EDITOR}</div>
		<div id="fragOptions">{TAB_OPTIONS}</div>
	</div>';
	
	$strFragEditorContent = '
		<form action="'.$_SERVER["REQUEST_URI"].'" method="POST" id="form_editor" onSubmit="return actionClickOnSaveHtml(this,\''.$pfile->getUrl().'\',\''.urljsencode($_GET['file']).'\');" style="text-align:right">
			<div class="fckEditor">
				'.$oFCKeditor->CreateHtml().'
			<div class="panelHistory">
				<h4>'._('History').'</h4>
				{HISTORY_CONTENT}
			</div><!-- end history -->
			</div><!-- end fckblock -->
			<!-- buttons -->
			<div style="text-align:right;clear:both;">
				<div id="divInfoSaveHTML" style="float:left;display:none;">&nbsp;</div>
				<input type="button" class="pcmButton" onClick="MyCancel();return false;" value="'._('cancel').'" />
				<input type="button" class="pcmButton" onClick="$(\'.openTabs:first\').trigger(\'click\');return false;" value="'._('history').'" />
				<input type="submit" class="pcmButton" onClick="this.form.elements[\'view\'].value=true;return true;" value="'._('save & see').'" />
				<input type="submit" class="pcmButton" style="margin-right:0px;" value="'._('save').'" />
			</div>
			<input type="hidden" value="false" name="view" />
			<input type="hidden" name="todo" value="save" />
		</form>
	';
	/**
	 * GESTION DE L'HISTORIQUE
	 */
	$strHistoryContent='<ul class="historylist">{LIST_HISTORY}</ul>';
	$strHtmlListHistory='';
	$tabHistory = &$pfile->getHistoryList();
	if(sizeof($tabHistory) == 0)
		$strHistoryContent = _('History empty !');
	else {
		$i=0;
		foreach($tabHistory as $elemHistory){
			$strHtmlListHistory.='<li><a class="itemHistory '.(($i==0)?'selected':'').'" href="#" onclick="loadHistoryPage(\''.$elemHistory['PATH'].'\',this);return false;">'.$elemHistory['PRINTNAME'].'</a></li>';
			$i++;
		}				
		$strHistoryContent = str_replace('{LIST_HISTORY}',$strHtmlListHistory,$strHistoryContent);
	}
	$strFragEditorContent = str_replace('{HISTORY_CONTENT}',$strHistoryContent,$strFragEditorContent);

	$strFragOptionsContent = $pfile->oPConfigFile->DisplayEditor();
	echo str_replace(array('{TAB_EDITOR}','{TAB_OPTIONS}'),array($strFragEditorContent,$strFragOptionsContent),$strTabsTpl);

} else{//if not html page
	echo $pfile->DisplayEditor();
}
?>

<?php
}//end user connected
include("admin_bottom.php");
?>
