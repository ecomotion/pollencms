<?php
if(!defined("PFile")){
	
	define("PFile",1);
	require('pofile.php');
	require('pdircategory.php');

class PFile extends POFile {
	
	/**
	 * Constructor of the class PFile.
	 * This object manage the file on the disk.
	 *
	 * @param string $strPath : the path of the file
	 * @return PFile : the object PFile
	 */
	function PFile($strPath){
		parent::POFile($strPath);
		if( !is_file($this->path) && is_file($strNewPath=utf8_decode($this->path)) ){$this->path=$strNewPath;}
	}
		
	function getMenuName(){
		return $this->getNameWithoutExt();
	}
	
	/**
	 * getNameWithoutExt
	 * Return the name of the current file without extension.
	 *
	 * @return string : the name of the file without extention.
	 */	
	function getNameWithoutExt(){
		return basename($this->path,".".$this->getExtension());
	}	
	/**
	* getPrintedName
	* 
	* @return string : file name formated for screen display.
	* @example toto_titi_25_09-2005.avi -> toto titi 25 09 2005
	*/
	function getPrintedName(){
		return $this->getName();
	}
	
	function getShortName(){
		return $this->getNameWithoutExt();	
	}
	
	/**
	 * getIdName
	 *
	 * @return string: the name as an ID, replace all special chars by "" and white spaces by "_"
	 */
	function getIdName(){
		return preg_replace("/[^-a-z0-9_]/i",'',str_replace(' ','_',$this->getNameWithoutExt()));
	}
	
	/**
	* getExtension
	* @return string : the extention of the file (ex: toto.avi -> avi)
	*/
	function getExtension(){
		$pathinfo = pathinfo($this->path);
		return (isset($pathinfo['extension']))?$pathinfo['extension']:'';
		
	}
	
	/**
	* Function: getFormatedSize
	* @return string : The size of the file as a string
	* @example 10 Ko
	*/
	function getFormatedSize(){       
		$iSize=filesize($this->path)/1024;
		$unit = ' Ko';
		if($iSize>1024){
			$unit=' Mo';
			$iSize/=1024;
		}
		if($iSize > 1024){
			$unit = ' Go';
			$iSize /= 1024;
			
		}
		return round($iSize,1).$unit;
	}	

	/**
	* getMimeIconUrl
	* @Desc if icon file exist in the mimeicons directory return it, else return the unknown file icon
	* 
	* @param int $thumb_size: the size of the thumb
	* @return string: url of the associated mimeicon.
	*/
	function getMimeIconUrl($thumb_size=100){
		global $configFile;
		$mimeicon_dir="core/admin/theme/images/mimesicons";
		$mimeicon_file=$mimeicon_dir."/".strtolower($this->getExtension()).".png";
		$mime="";
		if(is_file(SITE_PATH.$mimeicon_file)){
			$mime=$mimeicon_file;
		}else{
			$mimeicon_file=ereg_replace("\.png$",".gif",$mimeicon_file);
			if(is_file(SITE_PATH.$mimeicon_file)){
				$mime=$mimeicon_file;
			}else{
				$mimeicon_file=$mimeicon_dir."/unknown.png";
			}
		}
		return SITE_URL.$mimeicon_file;
	}
	
	/**
	* is_image
	* @return boolean : true if the file name repect the IMAGE_FILTER defined in config file.
	*/
	function is_image(){
		return eregi(IMAGE_FILTER,$this->getName());
	}

	/**
	* is_video
	* @return boolen : true if the file name repect the VIDEO_FILTER defined in config file.
	*/
	function is_video(){
		return eregi(VIDEO_FILTER,$this->getname());
	}
	
	function is_link(){
		return $this->getExtension()== 'lnk';
	}
	
	/**
	* Function: is_texteditable
	* return: return true if file name respect the TEXTEDIT_FILTER defined in config file
	*/
	function is_texteditable(){
		return eregi(TEXTEDIT_FILTER."|".TEXTEDIT_WYSWYG,$this->getname());
	}

	/**
	 * is_page
	 * Check if the file is a site page.
	 * In fact check that the extention of the file is html or htm or php.
	 * 
	 * @return boolean : true if current file is a site page.
	 */
	function is_page(){
		return preg_match('/htm$|html$|php$/',$this->getExtension());
	}
	function is_page_model(){
		if($this->is_page()){
			if(strstr($this->path,PAGES_MODELS_PATH)){
				return true;
			}
		}
		return false;
	}
	/**
	 * Test the current object file path. If in the PAGES_PATH return true, else return false.
	 *
	 * @return true if curr obj file is a site category
	 */
	function is_dircategory(){
		if(is_dir($this->path)){
			if( strpos($this->path,PAGES_PATH)!==false || strpos($this->path,PAGES_MODELS_PATH)!==false )
				return true;
		}
		if(is_dir($newpath=utf8_decode($this->path))){
			if(strstr($this->path,PAGES_PATH)!==false || strpos($this->path,PAGES_MODELS_PATH)!==false ) 
				return true;
		}
		return false;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return boolean : true if the file is a configfile, else false.
	 */
	function is_configfile(){
		return eregi("\.ini",$this->getname());
	}
	
	function getParentRelativePath(){
		$oPDirParent = &$this->getParentDir();
		return $oPDirParent->getRelativePath();
	}
	
	/**
	* Function: Display
	* return: echo the html code to display the file.
	*/
	function Display($thumb_size,$url=false,$oRootDir=false){
		$strReturn='';
		if(!$url) $url=$this->getDisplayUrl();
		$strReturn .= '<dl class="file" id="filename='.str_replace(SLASH,'/',$this->getRelativePath()).'">'."\n"; 
		$this->DisplayMenu(($oRootDir?$oRootDir->getRelativePath():''));

		$strReturn .= "\t<dt>\n\t\t<a class=\"fileLink\" href=$url>";
		$strReturn .= "<img src='".$this->getMimeIconUrl($thumb_size)."'"; 
		$strReturn .= " id=\"context_menu_".$this->getIdName()."\" ";
 		$strReturn .= " />";
 		$strReturn .= "</a>\n\t</dt>\n";
		$strReturn .= "\t<dd><span>".$this->getPrintedName()."</span></dd>\n";
		$strReturn .= "</dl>\n";

		return $strReturn;
	}
	
	/**
	 * getUrl
	 * return the url of the file.
	 * 
	 * @return strin : the url of the file
	 */	
	function getUrl(){
		$strRelativePath = $this->getRelativePath();
		if(SLASH != '/')
			$strRelativePath = str_replace(SLASH,'/',$strRelativePath);
		return SITE_URL.str_replace(rawurlencode('/'),'/',rawurlencode($strRelativePath));	
	}

	/**
	* For file like image return the direct file	
	*/
	function getDisplayUrl(){
			return '"'.$this->getUrl().'" target="_blank"';
	}	
	
	function DisplayMenu($strRootPath=false){
		$url=$_SERVER["REQUEST_URI"];
		if(!eregi("\?",$url)){
			$url.="?t=1";
		}
		echo "\t<div class=\"contextMenu\" id=\"menu_".$this->getIdName()."\">\n<ul>\n";
			echo $this->menuDelete();
			echo $this->menuRename();
			echo $this->menuCopy();
			if(defined("EDIT_FILE")){
				echo $this->menuMove($strRootPath);
				echo $this->menuEdit($url);
			}
			echo $this->getMenuSpecificsItems();
			echo "</ul>\n</div>\n";
	}
	
	function getMenuSpecificsItems(){
		$strMenu ='';
		doEventAction('file_menu',array(&$this,&$strMenu));
		return $strMenu;	
	}
	
	function menuDelete($strType=false){
		$strType = ($strType===false)?_('the file'):$strType;
		$strName = str_replace("'","\'",$this->getPrintedName());
		return "<li><a href=\"javascript:deleteFile('".urljsencode($this->getRelativePath())."','".$strName."','".$strType."');\" id=\"delete\" title=\""._("Delete")."\">"._("Delete")."</a></li>";
	}
	
	function menuRename(){
		$strName = str_replace("'","\'",$this->getName());
		return '<li><a href="javascript:fileRenameAjax(\'filename='.urljsencode($this->getRelativePath()).'\');" id="rename" title="'._('Rename').'">'._('Rename').'</a></li>'."\n";
	}
	
	function menuCopy(){
		$strName = str_replace("'","\'",$this->getPrintedName());
		return "<li><a href=\"javascript:copy('".urljsencode($this->getRelativePath())."','".$strName."');\" id=\"file_copy\" title=\""._("Copy")."\">"._("Copy")."</a></li>";
	}

	function menuMove($rootpath=''){
		$objCurrDir = new PDir($this->getParentPath());
		$strCurrDir = $objCurrDir->getRelativePath(SITE_PATH.$rootpath);
		
		return '<li><a href="javascript:move(\''.urljsencode($this->getRelativePath()).'\',\''.urljsencode($strCurrDir).'\',\''.urljsencode($rootpath).'\');" id="folder_move" title="'._('Move').'">'._('Move').'</a></li>';
	}

	function menuEdit($url){
		return '';
	}
	
	/**
	 * Delete
	 * Delete the file.
	 * First check that the file is writable, then unlink it.
	 * If oFile is not a file, return true because it means that the file doesn't exist any more.
	 *
	 * @return boolean : true if suceed, else false
	 */
	function Delete(){
		if(!is_file($this->path)) return true;
		if(!is_writable($this->path)) 
			return setError(sprintf(_("Can not erase file %s .\nCheck file permissions."), $this->getPrintedName()));
		if(!unlink($this->path)) return setError(_("Can not erase file")." $this->getRelativePath().");
		return true;
	}
	
	/**
	 * Rename a file. If the newname has no file extension, use the current file extension.
	 * If destDir not set, use the current file directory. If set move the file to the
	 * destdir.
	 *
	 * @param string $strNewName, the new name of the file.
	 * @param string $destDir, the path of the destination directory, if false, the parent path
	 * @return true if succeed, else false
	 */
	function Rename($strNewName, $destDir=false){
		$strNewName = $this->getUnixName($strNewName);
		$strActionName = (!$destDir || $destDir == $this->getParentPath())?'rename':'move';
		
		//check name
		if(!$this->checkname($strNewName))
			return false;

		//check extension
		$oFile = new PFile($strNewName);
		$strNewName .= ($oFile->getExtension()=='')?'.'.$this->getExtension():'';

		if(strlen($oFile->getNameWithoutExt())==0) 
			return setError(_("Can not $strActionName with empty name."));
			
		//check destDir
		$destDir = (!$destDir)?$this->getParentPath():$destDir;
		$objDstDir = new PDir($destDir);
		if(!$objDstDir->isDir())
			return setError(sprintf(_("Can not $strActionName file.\nDirectory not %s exists."),$objDstDir->getRelativePath()));		
		if( !is_writable($objDstDir->path) )
			return setError(sprintf(_("Can not $strActionName file %s.\nDirectory is not %s writable."), $objDstDir->getRelativePath()));
			
		//check write accesses
		if(!is_writable($this->path)) 
			return setError(sprintf(_("Can not $strActionName file %s.\n"), $this->getRelativePath()));
		$newfile=$destDir.SLASH.$strNewName;
		if($this->path == $newfile) return true;
		
		if( file_exists($newfile) )
			return setError(sprintf(_("File: %s exists."),basename($newfile)));
		
		if(!@rename($this->path,$newfile)) 
			return setError(_("An error occured while renaming file"));
		
		$this->path=$newfile;
		return true;				
	}
	
	/**
	 * Move
	 * Move a file in an other directory
	 *
	 * @param string $strDestDir, the path to the new directory (must exists !!)
	 * @return boolean : true if succeed, else false.
	 */
	function Move($strDestDir){
		return $this->Rename($this->getName(), $strDestDir);
	}
	
	function Copy($newname,$parent_path=false){
		if( !$this->checkName($newname) ) 
			return false;

		//check extension
		$oFile = new PFile($newname);
		$newname .= ($oFile->getExtension()=='')?'.'.$this->getExtension():'';

		//check parent path access
		if(!$parent_path)
			$parent_path=$this->getParentPath();
		if( !is_writable($parent_path) )
			return setError(_("can not copy. Directory not writable.\n Check file permissions."));
			
		$newfile=$parent_path.SLASH.$newname;
		if(is_file($newfile) || is_dir($newfile))	return setError(_("File exists").": ".$newname);
		if(!copy($this->path,$newfile)) return setError(_("Error occured while copying file").". "._("Check file permissions").".");
		return true;				
	}
	
}//end of class

	/**
	 * getFileObject
	 * Return the file object depending of its type.
	 * For example is the path is a directory, return a PDir object.
	 *
	 * @param string $strFilePath, the full path to the file
	 * @return the file object PPage, PDir ....
	 */
	function getFileObject($strFilePath){
		$objFile= new PFile($strFilePath);
		
		if(is_dir($objFile->path))
			return ($objFile->is_dircategory())?new PDirCategory($strFilePath):new PDir($strFilePath);
		if(is_dir($newpath=utf8_decode($objFile->path)))
			return ($objFile->is_dircategory())?new PDirCategory($newpath):new PDir($newpath);
		
		if($objFile->is_link()) return new PLink($strFilePath);
		if($objFile->is_configfile()) return new PConfigFile($strFilePath,(basename($strFilePath)!=basename(CONFIG_FILE))?CONFIG_FILE:false);	
		if($objFile->is_image()) return  new PImage($strFilePath);	
		//if($objFile->is_video()) $objFile = new PVideo($file);
		if($objFile->is_page_model()) {
			require(SITE_PATH.'core/lib/ppagemodel.php');
			return new PPageModel($strFilePath);
		}
		if($objFile->is_page()) return new PPage($strFilePath);
		if($objFile->is_texteditable()) return new PTextFile($strFilePath);	
		return $objFile;
	}
	
	function getFileObjectAndFind($strPath, $filter='findtype'){
		if(file_exists($strPath)) return getFileObject($strPath);
		//cut the file number
		$objFile= new PFile($strPath);
		$objParentDir = new PDir($objFile->getParentPath());
		switch($filter){
			case 'file':
				$filter = $objParentDir->ONLY_FILES;
				break;
			case 'dir':
				$filter = $objParentDir->ONLY_DIR;
				break;
			case 'all':
				$filter = $objParentDir->ALL;
				break;
			case 'findtype':
				$filter = (preg_match('/\.[a-z]+$/i',$strPath)? $objParentDir->ONLY_FILES:$objParentDir->ONLY_DIR);
				break;
			case false:
				$filter = $objParentDir->ONLY_FILES;
				break;
			default:
				$filter = $objParentDir->ONLY_FILES;
				break;
		}
		$strSimplePath =  basename($strPath);
		if( !($tabFile = $objParentDir->listDir($filter, true, $strSimplePath.(($filter==$objParentDir->ONLY_DIR)?'$':''))) )
			return false;
		foreach($tabFile as $strPathFileFiltered){
			$strFileName = basename($strPathFileFiltered);
			if($strFileName == $strSimplePath){
				return getFileObject($strPathFileFiltered);
			}
		}
		
		return setError(sprintf(_("Fatal Error, can not find the file %s"),$strSimplePath));
	}
	
}
