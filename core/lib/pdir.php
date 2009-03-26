<?php
if(!defined("PDIR")){
define("PDIR",0);

require ('pfile.php');
require ('lib_error.php');

class PDir extends POFile {
	
	var $ALL=0;
	var $ONLY_FILES=1;
	var $ONLY_DIR=2;
	var $ORDER_NAME=0;
	var $ORDER_TYPE=1;
	
	function PDir($path) {
		parent::POFile($path);
		if(substr($path,-1)=="/")
			$path=substr($path,0,strlen($path)-1);
		if(is_dir($newpath=utf8_decode($this->path))) $this->path=$newpath;
	}
	/**
	 * isDir
	 *
	 * @return true if the current object is a directory
	 */
	function isDir(){
		return is_dir($this->path);
	}

	function listDir($options=0,$fullpath=true,$filter=".*",$filterFalse=false,$nofilter=false,$iTypeOrder=0){
		if(!is_dir($this->path))
			return setError("Fatal Error in lisDir: $this->path is not a directory !!");
		
		$strDefaultFilter="^Thum*|~$|DS_Store|\.ini$|\.svn$".(($filterFalse!=false)?"|".$filterFalse:"");
		$d = dir($this->path);
		$tabReturn = array();
		while($entry = $d->read()) {
			if ($entry != "." && $entry != ".." && eregi($filter,$entry)) {
				if($nofilter==true || !eregi($strDefaultFilter,$entry)){
					$toreturn=$this->path.SLASH.$entry;
					if($fullpath==false) $toreturn=$entry;
					switch ($options){
						case $this->ONLY_FILES:
						if(is_file($this->path.SLASH.$entry))
							array_push($tabReturn,$toreturn);
						break;
						case $this->ONLY_DIR:
							if(is_dir($this->path.SLASH.$entry))
								array_push($tabReturn,$toreturn);
						break;
						default:
							array_push($tabReturn,$toreturn);
						break;
					}//end of switch
				}
			}
		}
		$d->close();
	        sort($tabReturn);
	        reset($tabReturn);
		return $tabReturn;
	}

	function listDirObject($options=0,$filter=".*", $filterFalse=false,$nofilter=false,$iTypeOrder=0){
		$tabList = $this->listDir($options,true,$filter, $filterFalse,$nofilter,$iTypeOrde);
		$tabReturn=array();
		foreach($tabList as $strFilePath){
			$o=getFileObject($strFilePath);
			$tabReturn[$o->getName()]=$o;
		}
		return $tabReturn;
	}

	function getPrintedName(){
		return $this->getName();
		//nb files
		$nb_files = sizeof($this->listDir($this->ONLY_FILES));
		$nb_dir = sizeof($this->listDir($this->ONLY_DIR));
		$return="<span>$name</span><br />";
		if($nb_files > 0)
			$return.="$nb_files f";
		if($nb_dir > 0){
			if($nb_files > 0)
				$return.=" / ";
			$return .= "$nb_dir d";
		}
		return $return;
	}

	function getIdName(){
		return preg_replace("/\(|\)|'|é|è|à|ç|â|ô|ê|î|ë|ï|\+|\?/","",preg_replace("/ /","_",$this->getName()));
	}

	function getMimeIconUrl($print){
		global $configFile;
		$mimeicon_dir=str_replace('/',SLASH,'core/admin/theme/images/mimesicons');
		$mimeicon_file=$mimeicon_dir.SLASH.(strstr($print,'..')?'root-folder.gif':'folder.gif');
			
		if( !is_file(SITE_PATH.$mimeicon_file) )
			$mimeicon_file=$mimeicon_dir.SLASH.'unknown.png';
		
		return SITE_URL.str_replace(SLASH,'/',$mimeicon_file);
	}	
	
	function mkdir(){
		if(is_dir($this->path))
			return true;
		$pDirParent = new PDir($this->getParentPath());
		if(!is_dir($pDirParent->path))
			if(!$pDirParent->mkdir())
				return false;
		if(!mkdir($this->path)) return setError(_("Can not create directory (check permissions): ").$this->getName);
		return true;
	}

	function Display($thumb_size,$print=false,$url=false,$proot_dir=false){
		echo "<dl class=\"folder file\" id=\"filename=".$this->getRelativePath()."\">\n";
		$this->DisplayMenu((($proot_dir)?$proot_dir->getRelativePath():''));
		if(!$url) $url=$_SERVER["PHP_SELF"]."?current_dir=".urlencode($this->getRelativePath((($proot_dir)?$proot_dir->path:SITE_PATH))).(($proot_dir)?"&rootpath=".urlencode($proot_dir->getRelativePath()):'');
		echo "\t<dt><a href='$url'>";
		echo "<img align=\"top\"  src='".$this->getMimeIconUrl($print)."' alt=''  ";
		if(!$print)
			echo ' id="context_menu_'.$this->getIdName().'" ';
	
		echo "/></a></dt>\n";
		echo "\t<dd>";
		if(!$print)
			echo '<span>'.$this->getPrintedName().'</span>';
		else
			echo $print;//use in .. display
		echo "</dd>\n";
		echo "</dl>\n";
	
	}
	
	function DisplayMenu($rootpath=false){
		$url=$_SERVER["REQUEST_URI"];;
		if(!eregi("\?",$url))
			$url.="?t=1";
			
		echo "\t<div class=\"contextMenu\" id=\"menu_".$this->getIdName()."\">\n<ul>\n";
			echo $this->menuDelete($url);
			echo $this->menuRename($url);
			echo $this->menuCopy($url);
			if(defined("EDIT_FILE"))
				echo $this->menuMove($rootpath);
			if(defined("EDIT_FILE"))
				echo $this->menuEdit($url);
			if(defined("EDIT_FILE"))
				echo $this->menuEditContent($url);
			echo $this->getMenuSpecificsItems();	
		echo "</ul></div>\n";
	}
	
	function getMenuSpecificsItems(){
		
		return '';
	}
	
	function menuDelete($url, $strType=false){
		$strType = ($strType===false)?_('the directory'):$strType;
		$strName = str_replace("'","\'",$this->getPrintedName());
		return "<li><a href=\"javascript:deleteFile('".urljsencode($this->getRelativePath())."','".$strName."','".$strType."');\" id=\"delete\" title=\""._("Delete")."\">"._("Delete")."</a></li>";
			}
	
	function menuRename($url){
		return "<li><a href=\"javascript:fileRenameAjax('filename=".urljsencode($this->getRelativePath())."');\" id=\"rename\" title=\""._("Rename")."\">"._("Rename")."</a></li>";
	}
				
	function menuCopy($url) {
		$strName = str_replace("'","\'",$this->getPrintedName());
		return "<li><a href=\"javascript:copy('".urljsencode($this->getRelativePath())."','".$strName."');\" id=\"folder_copy\" title=\""._("Copy")."\">"._("Copy")."</a></li>";
	}
	
	function menuMove($rootpath=''){
		$objCurrDir = new PDir($this->getParentPath());
		$strCurrDir = $objCurrDir->getRelativePath(SITE_PATH.$rootpath);
		return '<li><a href="javascript:move(\''.urljsencode($this->getRelativePath()).'\',\''.urljsencode($strCurrDir).'\',\''.urljsencode(urlencode($rootpath)).'\');" id="folder_move" title="'._('Move').'">'._('Move').'</a></li>';
	}
	
	function menuEdit($url=""){
		return '';
	}
	
	function menuEditContent($url=''){
		return '';
	}

	function createFile($filename){
		if(!$this->checkname($filename))
			return false;
		if($filename == "") return setError("cannot create a file without name. You must enter a file name.");
		if(preg_replace('/\..*$/','',$filename)=='') return setError('Mauvais nom de fichier');	
		if(!is_writable($this->path)) return setError("Can not create file. Check file permissions.");
		if(!preg_match('/\.[a-z]*$/',$filename)) $filename .=".html";
		if(is_file($this->path.SLASH.$filename)) return setError("file exists");
		//if file do not have extention add html extension
		$file=fopen($this->path.SLASH.$filename,"w");
		if(!$file) return setError("error while creating file");
		fclose($file);
		return true;
	}

	function createDir($dirname){
		if($dirname == '') return setError("cannot create a folder without name. You must enter a folder name.");	
		if(!is_writable($this->path)) return setError("Can not create folder. Check file permissions.");
		if(is_dir($this->path.SLASH.$dirname)) return setError("folder exists");
		if(!mkdir($this->path.SLASH.$dirname)) return setError("can not create folder");
		return true;
	}

	function uploadFile($_file,$fieldname="sendedfile"){
	 	// $_FILES['nom_du_fichier']['error'] vaut 0 soit UPLOAD_ERR_OK
	 	// ce qui signifie qu'il n'y a eu aucune erreur
		if ($_file[$fieldname]['error']) {
          switch ($_file[$fieldname]['error']){
                   case 1: // UPLOAD_ERR_INI_SIZE
                   return strError("Le fichier dépasse la limite autorisée par le serveur !");
                   break;
                   case 2: // UPLOAD_ERR_FORM_SIZE
                   return strError("Le fichier dépasse la limite autorisée dans le formulaire HTML !");
                   break;
                   case 3: // UPLOAD_ERR_PARTIAL
                   return strError("L'envoi du fichier a été interrompu pendant le transfert !");
                   break;
                   case 4: // UPLOAD_ERR_NO_FILE
                   return setError("Le fichier que vous avez envoyé a une taille nulle !");
                   break;
          }
		}
		else {
			$strFileName = $this->getUnixName($_file[$fieldname]['name']);
			if(!is_writable($this->path)) return setError("can not write in this directory check permissions.");
			if( !$this->checkName($strFileName) )
				return setError($strFileName);
			if( is_file($this->path.SLASH.$strFileName) )
				return setError("file ".$strFileName." ever exists.\n Please delete it or change upload file name.");
	 		if(!move_uploaded_file($_file[$fieldname]['tmp_name'], $this->path.SLASH.$strFileName))
	 			return setError("Error occured while moving uploaded file.");
		}
		return true;
	}//end function upload

	function Delete(){
		if(!is_dir($this->path)) return true;
		if(!is_writable($this->path)) return setError(sprintf(_("can not erase dir %s Check permissions."),$this->getRelativePath()));
		//erase all documents inside dir
		$tabList = $this->listDir($this->ALL,true,".*",false,true);
		foreach($tabList as $file){
			$ofile= getFileObject($file);
			if(!$ofile->Delete()) return false;
		}
		if(!rmdir($this->path)) return setError(sprintf(_("Error while erasing dir %s. Check permissions."),$this->getRelativePath()));
		return true;
	}
	
	function Rename($newname,$destDir=false){
		if(strlen($newname)==0) return setError("Can not rename with empty name");
		if(!$destDir) $destDir = $this->getParentPath();

		if(!is_writable($this->path)) return setError("can not rename dir ".$this->getRelativePath()." Check permissions.");
		$newfile=$destDir.SLASH.$newname;
		if($this->path == $newfile) return true;
		if(is_file($newfile) || is_dir($newfile))	return setError('dir '.$newname.' exists');
		if(!rename($this->path,$newfile)) return setError("error occured while renaming dir");
		return true;				
	}
	
	/**
	 * Move the directory and sub files/dir to an other directory
	 *
	 * @param sttring $strDirDest, the directory path
	 * @return true if succeed
	 */
	function Move($strDirDest){
		if(!is_dir($strDirDest))
			return setError(sprintf(_('Can not move %s. The target directory not exists !'),$this->getName()));
		return $this->Rename($this->getName(), $strDirDest);
	}
	
	function Copy($newname,$dirDest=false){
		if(strlen($newname)==0) return setError(_("Can not copy directory with empty name"));
		if(!$dirDest) $dirDest=$this->getParentPath();
		$newDir= new PDir($dirDest.SLASH.$newname);
		if(is_file($newDir->path) || is_dir($newDir->path))	return setError(_("Directory or File exists").": ".$newname);
		if(!$newDir->mkdir())
			return false;
		$tabFiles = $this->listDir($this->ALL,$fullpath=true);
		foreach($tabFiles as $file){
			$objFile = getFileObject($file);
			if(!$objFile->Copy($objFile->getName(),$newDir->path))
					return false;	
		}
//			if(!copy($this->path,$newfile)) return setError(_("Error occured while copying directory").". "._("Check file permissions").".");
		return true;				
	}
	
	function getLinkPath($rootpath=false){
		$oDir = new PDir($this->path);
		$root=($rootpath!=false)?$rootpath:SITE_PATH.SITE.PAGES;
		$tabPath = explode(SLASH,$oDir->getRelativePath($root));
		$pdirroot =  new PDir($rootpath);
		$rootpath = $pdirroot->getRelativePath();
		$parent='';
		$isize=sizeof($tabPath);
		for($i=0;$i<$isize;$i++){
			$path=$tabPath[$i];
			if( $path != ''){
				$parent.=$path;
				$obj = getFileObject($root.SLASH.$parent);
				echo " > <a href=\"".$_SERVER["PHP_SELF"]."?current_dir=".urlencode($parent).(($rootpath==false)?"":"&rootpath=".$rootpath)."\" >".$obj->getPrintedName();
				echo "</a>";
				$parent.=SLASH;
			}
		}
	}
		
}//end class
}//end ifdefine
?>
