<?php
if(!defined("PTEXTFILE")){
define("PTEXTFILE",0);
include "pfile.php";

class PTextFile extends PFile {

	function PTextFile($strPath){
		parent::PFile($strPath);
	}
	
		function menuEdit($url_callback){
			return "<li><a href=\"admin_file_editor.php?file=".urlencode($this->getRelativePath())."\" id=\"edit\" title=\""._("Edit")."\">"._("Edit")."</a></li>";
		}
		function menuPreview($url){
			return "";
			return "<a target=\"_blank\" href=\"index.php?page=".urlencode(substr($this->getRelativePath(),strlen(PAGES_PATH)+1))."\" id=\"preview\" title=\""._("Preview")."\">"._("Preview")."</a>";
		}

	function _Modified($strText){
		if(!is_file($this->path)) return true;

		$strText=stripslashes($strText);
		$content = file_get_contents($this->path);
		return !($content == $strText);
	}
	
	function Save($text, $bStripSlashes=true){
		//remove due to serialize bug $text=stripslashes($text);
		if($bStripSlashes)
			$text=stripslashes($text);
		
		if(is_file($this->path) && !is_writable($this->path)) 
			return setError(sprintf(_("Can not open file %s for writing."),$this->getName()).' '._("Check file permissisons"));

		if(!is_dir($this->getParentPath())){
			$oDirParent = $this->getParent();
			if(!$oDirParent->mkdir())
				return false;
		}

		$fic = fopen($this->path,"w");
		if(!$fic)	return setError(sprintf(_("Can not open file %s for writing."),$this->getName()).' '._("Check file permissisons"));
		
		//replace the $text
		if($this->getExtension() == 'xml')
			$text = str_replace(array('<textareatag','</textareatag'),array('<textarea','</textarea'),$text);
		
		if(strlen($text) > 0){
			if(fwrite($fic,$text) == FALSE){
				setError(_("An error occur while writing text"));
				fclose($fic);
				return false;
			}
		}
		fclose($fic);
		return true;
	}
	
	function getEditorFileContent(){
		$strContent = 	file_get_contents($this->path);
		return str_replace('textarea','textareatag',$strContent);
	}
	
	function DisplayEditor(){
		//due to ie bug, we cannot use event as onSubmit, onClick in ajax result request !!, so we use links
		$strId = 'form_editor_config_'.preg_replace('/[^a-zA-Z]/','',$this->getRelativePath());
		$strTpl = '
		<form action="'.$_SERVER["REQUEST_URI"].'" method="POST" id="'.$strId.'" onSubmit="return false">		
			<textarea style="width:100%;height:220px;border:1px solid #000;" name="text" id="text">{VALUE}</textarea>
			<input type="hidden" name="todo" value="savetextfile" />
			<input type="hidden" name="filepath" value="{PATH}" />
			<a href="javascript:actionClickOnSaveTxt(\''.$strId.'\',\''.urljsencode($this->getRelativePath()).'\');" style="float:right;margin-right:0px;" class="pcmButton">'._('Save').'</a>
		</form>
		<div class="reset"></div>
		';
		return str_replace(array('{PATH}','{VALUE}'),array($this->path,$this->getEditorFileContent()),$strTpl);
		
	}
	
		
	
}//end class

}//define	
?>