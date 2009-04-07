<?php
if(!defined("PPAGEMODEL")){
define("PPAGEMODEL",1);
require(SITE_PATH.'core/lib/ppage.php');

/**
 * Class PPAGEMODEL.
 * This class is used to manipulate pages models.
 */

class PPageModel extends PPage {

	function PPageModel($strPath){
		parent::PPage($strPath);
	}
	
	
	function getMimeIconUrl($thumb_size){
		$strThumbFile = $this->getParentPath().SLASH.'images'.SLASH.$this->getNameWithoutExt().'.gif';
		if(is_file($strThumbFile)){
			require(SITE_PATH.'core/lib/pimage.php');
			$oThumb = new PImage($strThumbFile);
			return $oThumb->getMimeIconUrl($thumb_size);
		}
		return parent::getMimeIconUrl($thumb_size);
	}

	function DisplayEditor(){
		$strReturn = '';
		
		$strFCKConfigDir = SITE.SLASH.'config'.SLASH.'fckeditor'.SLASH;
		$strFCKConfigUrl = SITE_URL.((SLASH=='/')?$strFCKConfigDir:str_replace(SLASH,'/',$strFCKConfigDir)); 
		$strFCKConfigDir = CONFIG_DIR.'fckeditor'.SLASH;
		
		$oFCKeditor = new FCKeditor('text') ;
		$oFCKeditor->BasePath = SITE_URL.'vendors/jscripts/fckeditor/';
		$oFCKeditor->Value = $this->getEditorFileContent();
		$oFCKeditor->Height = '400' ;
		
		//the styles to apply to the editor, the page style and the fckeditor.css style
		$fileStyleUrls='';
		if($pageStyle = $this->getConfig("PAGE_STYLE")) $fileStyleUrls .= THEME_URL.'css/'.$pageStyle.',';
		if(is_file($strFCKConfigDir.'fckeditor.css')) $fileStyleUrls .= $strFCKConfigUrl.'fckeditor.css';
		$oFCKeditor->Config["EditorAreaCSS"] =$fileStyleUrls;
		
		$oFCKeditor->Config["ImageBrowserURL"] = SITE_URL."core/admin/admin_file_selector.php?rootpath=".urlencode(POFile::getPathRelativePath(MEDIAS_PATH));
		$oFCKeditor->Config["FlashBrowserURL"] = SITE_URL."core/admin/admin_file_selector.php?rootpath=".urlencode(POFile::getPathRelativePath(MEDIAS_PATH));
		//go to the parent path
		$oFCKeditor->Config["LinkBrowserURL"] = SITE_URL."core/admin/admin_file_selector.php?current_dir=".urlencode(POFile::getPathRelativePath($this->getParentPath(), PAGES_PATH))."&rootpath=".urlencode(POFile::getPathRelativePath(PAGES_PATH));
		$oFCKeditor->Config["CustomConfigurationsPath"] = SITE_URL.'index.php?page=fckconfig.js';
		
		if(is_file($strFCKConfigDir.'fcktemplates.xml'))
			$oFCKeditor->Config["TemplatesXmlPath"]	= SITE_URL.'index.php?page=fcktemplates.xml' ;
		if(is_file($strFCKConfigDir.'fckstyles.xml'))
			$oFCKeditor->Config["StylesXmlPath"]	= SITE_URL.'index.php?page=fckstyles.xml' ;
		
		
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
			<form action="'.$_SERVER["REQUEST_URI"].'" method="POST" id="form_editor" onSubmit="return actionClickOnSaveHtml(this,\''.$this->getUrl().'\',\''.urljsencode($_GET['file']).'\');" style="text-align:right">
				<div class="fckEditor">
					'.$oFCKeditor->CreateHtml().'
				<div class="panelHistory">
					<h4>'._('History').'</h4>
					{HISTORY_CONTENT}
				</div><!-- end history -->
				</div><!-- end fckblock -->
				<!-- buttons -->
				<div style="text-align:right;clear:both;">
					<button class="ui-state-default ui-corner-all" type="button" onClick="MyCancel();">'._('cancel').'</button>
					<button class="ui-state-default ui-corner-all" type="button" onClick="$(\'.openTabs:first\').trigger(\'click\');">'._('history').'</button>
					<button class="ui-state-default ui-corner-all" type="submit">'._('save').'</button>
				</div>
				<input type="hidden" value="false" name="view" />
				<input type="hidden" name="todo" value="save" />
			</form>
		';
		 // GESTION DE L'HISTORIQUE
		 
		$strHistoryContent='<ul class="historylist">{LIST_HISTORY}</ul>';
		$strHtmlListHistory='';
		$tabHistory = &$this->getHistoryList();
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
	
		$strFragOptionsContent = $this->oPConfigFile->DisplayEditor();
		
		$strReturn = str_replace(array('{TAB_EDITOR}','{TAB_OPTIONS}'),array($strFragEditorContent,$strFragOptionsContent),$strTabsTpl);
		return $strReturn;
	}
}
}//END DEFINE
?>