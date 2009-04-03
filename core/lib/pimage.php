<?php
if(!defined("PIMAGE")){
define("PIMAGE",0);
include "pfile.php";

class PImage extends PFile {

	function PImage($strPath){
		parent::PFile($strPath);
	}
	
	function getMenuSpecificsItems(){
		$menu = "\n".'<li><a href="javascript:resizeimage(\''.urljsencode($this->getRelativePath()).'\');" id="image_resize" title="'._('Resize').'">'._('Resize').'</a></li>';
		$menu .=''.parent::getMenuSpecificsItems();
		return $menu;	
	}
	
	/**
	 * Return the printed name of the image
	 *
	 * @return the printed name of the image
	 */
	function getPrintedName(){
		$strName=$this->getNameWithoutExt();;
		if(strlen($strName)/16>1){
			$iWords=intval(strlen($strName)/16)+1;
			$strFullName = $strName;
			$strName='';
			for($i=0;$i<$iWords;$i++){
				$strName.=substr($strFullName,$i*16,($i+1)*16).'<br>';
			}
		}
		return $strName;
	}
	
	/**
	 * Return the resolution string of the current image widthxheight
	 *
	 * @return string the string of the image resolution
	 */
	function getResolution(){
		list($width, $height) = getimagesize($this->path);
		return $width."x".$height;
	}
	
	/**
	 * Return the width of the image.
	 *
	 * @return int the width of the image
	 */
	function getWidth(){
		list($width, $height) = getimagesize($this->path);
		return $width;
	}
	
	function getHeight(){
		list($width, $height) = getimagesize($this->path);
		return $height;
	}
	
	function createThumb($iTSize, $bSquare=true, $bForce=false, $strThumbPath=false){
		if( !$strThumbPath )
			$objThumb= new PImage(CACHE_DIR."thumbnails/".$iTSize."x".$iTSize."/".$this->getRelativePath());
		else
			$objThumb= new PImage($strThumbPath);
		
		//if objThumb exists
		if(is_file($objThumb->path))
			if( !$bForce )
				return $objThumb;
			else if( !$objThumb->Delete() )
				return false;
		
		//create the directory
		$oDir = $objThumb->getParentDir();
		if( !is_dir($oDir->path) && !$oDir->mkdir())
			return false;
		
		if( !($this->Copy($this->getName(), $objThumb->getParentPath())) )
			return false;
		
		if ( !$objThumb->ResizeMax($iTSize, $bSquare) ){
			$objThumb->Delete();//if error occured while resizing thumb, delete it.
			return false;
		}
	
		return $objThumb;
	}
	
	function rotate($iAngle){
		if(USE_GD){
			$img = @imagecreatefromjpeg($this->path);
			$img_rotate = imagerotate($img, -$iAngle, 0);
			imagedestroy($img);
			imagejpeg($img_rotate,$this->path,100);		
			imagedestroy($img_rotate);
		}else{
			$command=CONVERT_PATH." -rotate ".$iAngle." \"".$this->path."\" \"".$this->path."\"";
			$result = exec($command);
			if($result)
				echo $result;
		}
	}
	
	function ResizeMax($iNewSize=640, $bSquare = false){
		$iWidth = $this->getWidth();
		$iHeight = $this->getHeight();
		$iSize = max($iWidth,$iHeight);
		
		if($iWidth == $iSize){
			$iTWidth = $iNewSize;
			$iTHeight = intval($iTWidth*($iHeight/$iWidth));
		}else {
			$iTHeight = $iNewSize;
			$iTWidth = intval($iTHeight*($iWidth/$iHeight));
		}
		return $this->Resize($iTWidth, $iTHeight, $bSquare);
	}
	
	function Resize($iTWidth, $iTHeight, $bSquare=false){
		if( !is_writable($this->path) )
			return setError(sprintf(_('Canot resize image %s. File is not writable.'), $this->getPrintedName()));

		$iWidth = $this->getWidth();
		$iHeight = $this->getHeight();
		$iWTBorder = $bSquare?intval((max($iTWidth,$iTHeight)-$iTWidth)/2):0;
		$iHTBorder = $bSquare?intval((max($iTWidth,$iTHeight)-$iTHeight)/2):0;
		
		if(!USE_GD && !is_file(CONVERT_PATH))
			return setError(sprintf(_('Can not resize image %s. Imagemagick binary not exists'),$this->getPrintedName()));
		
		if(!USE_GD){			
			$strBorder = (!$bSquare)?'':'-bordercolor white -border '.$iWTBorder.'x'.$iHTBorder;
			$strCommand = CONVERT_PATH.' -resize '.$iTWidth.'x'.$iTHeight.' '.$strBorder.' "'.$this->path.'" "'.$this->path.'"';
			exec($strCommand);	
		}else{
			//first check that the image memory limit is ok, else change the memory limit
			$bNeedRestoreMemoryLimit = false;
			$iMB = Pow(1024,2);//number of bytes in 1M
			$iSysMemLimit = intval(ini_get('memory_limit'))*$iMB;
			$iSysNeeded = $this->_getImageMemorySize() + ((function_exists('memory_get_usage'))?memory_get_usage():0);//currentuse
			if( $iSysNeeded > $iSysMemLimit  ){
				ini_set( 'memory_limit', ceil($iSysNeeded/$iMB) . 'M' );
				$bNeedRestoreMemoryLimit = true;
				$iSysMemLimit = intval(ini_get('memory_limit'))*$iMB;
				if( $iSysNeeded > $iSysMemLimit )
					return setError(sprintf(_('Error in resizing image. Memory Limit is to low. Need %s M of memory.'),ceil($iSysNeeded/$iMB).''));
			}
			$strImgExt = strtolower($this->getExtension());
			switch(strtolower($this->getExtension())){
				case 'png':
					$oImg = @imagecreatefrompng($this->path);
				break;
				case 'gif':
					$oImg = @imagecreatefromgif($this->path);
				break;
				case 'jpeg':
					$oImg = @imagecreatefromjpeg($this->path);
				break;
				case 'jpg':
					$oImg = @imagecreatefromjpeg($this->path);
				break;
				default:
					return setError(sprintf(_('%s images are not supported by gd.'),$strImgExt));
				
			}
			if(!$oImg)
				return setError('Can not create image buffer.');
				
			//first resize the image
			$oImgResizedTmp = imagecreatetruecolor($iTWidth,$iTHeight);
			if( $strImgExt == 'png' && !$bSquare ){
        		imagealphablending($oImgResizedTmp, true);
        		imagesavealpha($oImgResizedTmp,true);
        		$transparent = imagecolorallocatealpha($oImgResizedTmp, 255, 255, 255, 127);
        		imagefilledrectangle($oImgResizedTmp, 0, 0, $iTWidth, $iTHeight, $transparent);
			}
			imagecopyresampled($oImgResizedTmp, $oImg, 0, 0, 0, 0, $iTWidth, $iTHeight, $iWidth, $iHeight);//need gd2
			imagedestroy($oImg);
			
			if(!$bSquare){
				$oImgResized = &$oImgResizedTmp;
			}else{
				//we add border to the image if we want a square
				$oImgResized = imagecreatetruecolor(max($iTWidth,$iTHeight), max($iTWidth,$iTHeight));
				imagefill($oImgResized,0,0,ImageColorAllocate( $oImgResized, 255, 255, 255 ));//fill background to white
				imagecopy($oImgResized,$oImgResizedTmp,$iWTBorder,$iHTBorder,0,0,$iTWidth,$iTHeight);
				imagedestroy($oImgResizedTmp);
			}
			
			//save image
			if($strImgExt=="png")
				imagepng($oImgResized,$this->path,100);
			if($strImgExt=="gif")
				imagegif($oImgResized,$this->path,100);
			else
				imagejpeg($oImgResized,$this->path,100);
			imagedestroy($oImgResized);
			$bNeedRestoreMemoryLimit && ini_restore('memory_limit');
		}
		
		/*if(!$bSquare){
			$iWidth = $this->getWidth();
			$iHeight = $this->getHeight();
			if( $iWidth != $iTWidth || $iHeight != $iTHeight )
				return setError(sprintf(_('An error occured while resizing image %s.'),$this->getPrintedName()));
		}*/
		return true;
	}
	/**
	 * Calculate the memory needed by an image in B.
	 * This function is used when we want to calculate the memory need by gd to create an image
	 *
	 * @return unknown
	 */
	function _getImageMemorySize(){
		if(!is_file($this->path))
			return 0;
		$iK64 = POW(2,16);//number of bytes in 64K
		$iTweakFactor = 2;
		
		$imageInfo = getimagesize($this->path);
		$memoryNeeded = round(($imageInfo[0] * $imageInfo[1] * $imageInfo['bits'] * $imageInfo['channels'] / 8 + $iK64) * $iTweakFactor);
		return $memoryNeeded;
	}
	
	function Display($thumb_size,$url=false,$oRootDir=false){
		if(!$url) $url=$this->getDisplayUrl();
		echo '<dl class="file image" id="filename='.str_replace(SLASH,'/',$this->getRelativePath()).'">'."\n"; 
		$this->DisplayMenu(($oRootDir?$oRootDir->getRelativePath():''));

		echo "\t<dt>\n\t\t<a  href=$url title='".$this->getName().' ('.$this->getResolution().")'>";
		echo "<img src='".$this->getMimeIconUrl($thumb_size)."'  width=\"".$thumb_size."\" alt=\"".$this->getNameWithoutExt()."\""; 
//		if(defined("EDIT_FILE"))
			echo " id=\"context_menu_".$this->getIdName()."\" ";
 		echo " />";
 		echo "</a>\n\t</dt>\n";
		echo "\t<dd><span>".$this->getPrintedName()."</span></dd>\n";
		echo "</dl>\n";	
	}

	
	function getMimeIconUrl($thumb_size){
		if( !($objThumb = $this->createThumb($thumb_size)) )
			return parent::getMimeIconUrl($thumb_size);
		return $objThumb->getUrl();
	}

	function getDisplayUrl(){
		if( !($objThumb = $this->createThumb(480,false)) )
			return parent::getDisplayUrl();

		return '"'.$objThumb->getUrl().'"  alt="'.$this->getPrintedName().'"';
	}
	
	function displayExifsInfos(){
		if(function_exists("exif_read_data")){
			$exif = exif_read_data($this->path, 0, true, false);
			foreach ($exif as $key => $section) {
   				foreach ($section as $name => $val) {
					if(!eregi("thumb|ModeArray|Undefined|ImageInfo|html",$key.$name)){
						echo "<strong>$name:</strong> $val<br />\n";
					}
				}
			}
		}else{
			echo "<strong>Name: </strong>".$this->getNameWithoutExt()."<br />\n";
			echo "<strong>File Size: </strong>".$this->getFormatedSize()."<br />\n";
			echo "<strong>Resolution: </strong>".$this->getResolution()."<br />\n";
		}
	}
	
	function _deleteCache(){
		//find thumbnails and delete all
		$oDirThumb= new PDir(CACHE_DIR."thumbnails");
		$tabDirSize = $oDirThumb->listDir($oDirThumb->ONLY_DIR,$fullpath=true);
		$strRelativePath=$this->getRelativePath();
		
		foreach($tabDirSize as $strDirPath){
			if(is_file($strThumbPath = $strDirPath.SLASH.$strRelativePath)){
				$oFileThumb = new PFile($strThumbPath);
				if( !$oFileThumb->Delete() )
					return false;
			}	
		}
		return true;
	}
	
	function Delete(){
		if( !$this->_deleteCache() )
			return false;
		return parent::Delete();
	}

	
}//end class
}//end def
