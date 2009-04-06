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
			$oThumb = &getFileObject($strThumbFile);
			return $oThumb->getMimeIconUrl($thumb_size);
		}
		return parent::getMimeIconUrl($thumb_size);
	}
}
}//END DEFINE
?>