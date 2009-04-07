<?php
if(!defined("POFile")){
	define("POFile",1);
	
/**
 * Directory / File Manipulation.
 * The class POFile is the top file level.
 * This class allow directory or file manipulation
 */
class POFile extends Directory {
	//a string to the path of the file or directory
	var $path='';
	/**
	 * The constructor of the class.
	 * @param string srtPath, the path of the file
	 * @author  Mathieu Vilaplana <mvilaplana@dfc-e.com>
	 */
	function POFile($strPath){
		$this->path = $strPath;
	}
	
	/**
	* function getName.
	* return the name of the file. It is in most case a simple basename on the file path.
	*
	* @author  Mathieu Vilaplana <mvilaplana@dfc-e.com>
	* @return string the name of the file (without parent path, a simple basename)
	*/
	function getName(){
		return basename($this->path);
	}
	
	function getPrintedName(){
		$this->getName();
	}
	
	function getPathRelativePath($strPath,$MYROOTPATH=SITE_PATH){
		return preg_replace('/^'.preg_quote($MYROOTPATH,'/').'('.preg_quote(SLASH,'/').')?/','',$strPath);
	}
	function getPathUrl($strPath){
		$strUrl = POFile::getPathRelativePath($strPath);
		if(SLASH != '/')
			$strUrl = str_replace(SLASH,'/',$strUrl);
		if(is_dir($strPath) && !preg_match('/\/$/',$strUrl))
			$strUrl.='/';//be shure that a directory url end with /
		return SITE_URL.$strUrl;
	}
	
	function getRelativePath($MYROOTPATH=SITE_PATH){
		if(strlen($MYROOTPATH)==0) return $this->path;
		return preg_replace('/^'.preg_quote($MYROOTPATH,'/').'('.preg_quote(SLASH,'/').')?/','',$this->path);
	}
	
	function getUrl(){
		return $this->getPathUrl($this->path);
	}
	
	/**
	* function getParentPath
	* @return string the parent path (ex: rep1/rep2/toto.avi -> rep1/rep2)
	*/
	function getParentPath(){
		$pathinfo = pathinfo($this->path);
		return $pathinfo["dirname"];
	}
	
	/**
	 * @return PDir object the parent directory
	 */
	function getParentDir(){
		return getFileObject($this->getParentPath());
	}
	
	/**
	 * It's an alias to getParentDir
	 *
	 * @return the parent Dir object
	 */
	function getParent(){return $this->getParentDir();}
		
	function getHeaderText(){
		$param = $this->getConfig("HEADER_TEXT");
		if( $param == 'AS_PARENT' || !$param || empty($param) ){
			$oParentCat = new PDirCategory($this->getParentPath());
			return $oParentCat->getTemplateName();
		}
		return $param;	
	}
	
	/**
	 * This function is called each time a file is created or renamed
	 * 
	 * @param $strName, the name to check, or if = false, check the object file name
	 * @return true if name is ok
	 */
	function checkName($strName=false){
		if(!$strName) $strName = basename($this->path);
		$oFile = new PFile($strName);
		if( !preg_match('/^[a-zA-Z0-9\.\s_-]*$/',$oFile->getNameWithoutExt()) )
			return setError(_('Special chars are not allowed.'));
		if( strlen($oFile->getNameWithoutExt()) == 0 )
			return setError(_('Empty name is not allowed'));
		return $this->checkExtension($strName);
	}
	/**
	 * Use when create or rename a file
	 *
	 * @param unknown_type $strName
	 */
	function checkExtension($strName = false){
		if(!$strName) $strName = basename($this->path);

		$pathinfo = pathinfo($strName);
		$strExt = (isset($pathinfo['extension']))?$pathinfo['extension']:'';
		if( preg_match('/php|cgi/i',$strExt) )
			return setError(sprintf(_('%s file extension is not allowed'),$strExt));
		return true;
	}
	
	function getUnixName($strName=false){
		if($strName === false) $strName = $this->getName();
		$remplace = array('à'=>'a',
                         'á'=>'a',
                         'â'=>'a',
                         'ã'=>'a',
                         'ä'=>'a',
                         'å'=>'a',
                         'ò'=>'o',
                         'ó'=>'o',
                         'ô'=>'o',
                         'õ'=>'o',
                         'ö'=>'o',
                         'è'=>'e',
                         'é'=>'e',
                         'ê'=>'e',
                         'ë'=>'e',
                         'ì'=>'i',
                         'í'=>'i',
                         'î'=>'i',
                         'ï'=>'i',
                         'ù'=>'u',
                         'ú'=>'u',
                         'û'=>'u',
                         'ü'=>'u',
                         'ÿ'=>'y',
                         'ñ'=>'n',
                         'ç'=>'c',
                         'ø'=>'0',
     					 ' '=>'-'
		); 		
		$strName = strtolower(strtr($strName,$remplace));
		$strName = preg_replace('/\.{2,}/','.', $strName);
		$strName = preg_replace('/\.$/','', $strName);
		return preg_replace('/[^\.\-a-zA-Z0-9]/','',$strName);
	}
	
}//END CLASS
}//END DEFINE
?>