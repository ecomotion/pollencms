<?php
addPollenPlugin('extrapage','images_tools_extrapage_imagestoolsresize');
addPollenPlugin('uploadfile','images_tools_OnUpload_ResizeImage');
addPollenPlugin('addPluginsTabs','images_tools_TabConfig');

addSmartyPlugin('output','images_tools_addjs_fancyzoom_and_ifixpng');
addSmartyPlugin('output','images_tools_change_image_src_to_autoresize');


function images_tools_TabConfig($tabExtraPlugins){
	
	$oPlugin = new PPluginDir(dirname(__FILE__));
	$oConfigFile = &$oPlugin->oConfig;
	
	$tabExtraPlugins[]=array(
		'FRAG_NAME'=>'plugins_imagestools',
		'TAB_NAME'=>_('Images Tools'),
		'TAB_CONTENT'=>$oConfigFile->DisplayEditor()
	);
	
	return true;
}

function images_tools_extrapage_imagestoolsresize($strPage, &$site) {
	
	if($strPage != 'imagestoolsresize.php')
		return false;

	require (SITE_PATH.'core/lib/pimage.php');
	
	if( !isset($_GET['img']) || !isset($_GET['width']) || !isset($_GET['height']) )
	        return false;
	
	$strImgUrl = urldecode($_GET['img']);
	if( strstr($strImgUrl,'..') !== FALSE )
	        return false;

	//check that image is a sub file of POLLEN MEDIAS directory
	if( !($oDirUpload = getFileObjectAndFind(MEDIAS_PATH)) )
	        return false;
	if( strstr($strImgUrl,$oDirUpload->getUrl()) === FALSE)
	        return false;
	$strImgUrl = str_replace($oDirUpload->getUrl(),'',$strImgUrl);
	
	if( !($oImage = getFileObjectAndFind($oDirUpload->path.SLASH.str_replace('/',SLASH,$strImgUrl))) )
	        return false;
	        
	$iWidth = $_GET['width'];
	$iHeight = $_GET['height'];
	
	//generate the image resized, first copy the original image, then generate the resized image
	$oImageResize = new PImage(CACHE_DIR.'thumbnails'.SLASH.$iWidth.'x'.$iHeight.SLASH.$oImage->getRelativePath());

	//create resized image if not exits
	if( !is_file($oImageResize->path) ){
	        if(!is_dir($oImageResize->getParentPath())){
	                $oDir = new PDir($oImageResize->getParentPath());
	                if( !$oDir->mkdir() )
	                        return false;
	        }
	        if( !$oImage->Copy($oImageResize->getName(),$oImageResize->getParentPath()) )
	                return false;
	        if( !$oImageResize->Resize($iWidth, $iHeight) )
	                return false;
	}
	//at this point image must exists, if not return
	if( !is_file($oImageResize->path) )
		return false;
	
	//just set the header and read the image
	header('Content-type: image/'.$oImage->getExtension());
	readfile($oImageResize->path);
	return true;
}

/**
 * images_tools_OnUpload_ResizeImage
 * If the uploaded file is an image, and plugin size max > image size, resize the image
 * For example user send a 2048px image => resize to Size set in plugin config
 * 
 * @param oFile $oFileUploaded, file uploaded file
 * @return true if succeed
 */
function images_tools_OnUpload_ResizeImage(&$oFileUploaded) 
{
	require (SITE_PATH.'core/lib/pimage.php');
	
	$oPlugin = new PPluginDir(dirname(__FILE__));
	$oConfigFile = &$oPlugin->oConfig;
	
	if($oConfigFile->getDirectParam("UPLOAD_RESIZE") !== 'true')
		return true;

	if( !($iSize = $oConfigFile->getDirectParam('UPLOAD_RESIZE_SIZE')) ) 
		return true;

	$iSize = intval($iSize);

	if($oFileUploaded && $oFileUploaded->is_image() && max($oFileUploaded->getWidth(),$oFileUploaded->getHeight()) > $iSize ){
		if(!$oFileUploaded->ResizeMax($iSize))
			return false;
	}
	return true;
}

function images_tools_addjs_fancyzoom_and_ifixpng($source, &$smarty)
{
	global $configFile;//to get the body id

	$oPlugin = new PPluginDir(dirname(__FILE__));
	$oConfigFile = &$oPlugin->oConfig;
	
	$oDir = new PDirCategory(dirname(__FILE__));
	$strUrl = $oDir->getUrl();
	
	//FANCYZOOM EFFECT
	$bFZEffect = ($oConfigFile->getDirectParam("FANCYZOOM_EFFECT")=== "true")?true:false;
	if( $bFZEffect ){
		if( !($strFZParamFind = $oConfigFile->getDirectParam("FANCY_ZOOM_FIND")) )
			$bFZEffect=false;
		
		if( $bFZEffect && $strFZParamFind == 'auto' ){
			if( !($strFckId = $configFile->getDirectParam('FCK_BODYID')) )
				$bFZEffect=false;
			$strFZParamFind ='$("img","#'.$strFckId.'")';
		}
		else if( $bFZEffect && !preg_match('/^\$/',$strFZParamFind) )
			$strFZParamFind = '$("'.$strFZParamFind.'")';

		//$strFZParamFind = str_replace("\'",'"',$strFZParamFind);		
		if( !($strFZOptions = $oConfigFile->getDirectParam("FANCY_ZOOM_OPTIONS")) )
			$strFZOptions ='{}';
			
				
		$strFZDefaultOptions='imgDir:\''.$strUrl.'js/ressources/\'';
		if( $oConfigFile->getDirectParam("SERVER_RESIZE") === "true" ){
			$strFZDefaultOptions.=', imgResizeScript:\''.SITE_URL.'imagestoolsresize.php\'';
		}		
	}
	//IFIX PNG
	$bFPNGEffect = ($oConfigFile->getDirectParam("IFIXPNG_EFFECT")==="true")?true:false;
	if($bFPNGEffect ){
		if( !($strFPNGParamFind = $oConfigFile->getDirectParam("IFIXPNG_FIND")) )
			$bFPNGEffect=false;

		if( $bFPNGEffect && $strFPNGParamFind == 'auto' ){
			if( !($strFckId = $configFile->getDirectParam('FCK_BODYID')) )
				$bFPNGEffect=false;
			$strFPNGParamFind ="$('img[src$=\".png\"]','#".$strFckId."')";
		}
		else if( $bFPNGEffect && !preg_match('/^\$/',$strFPNGParamFind))
			$strFPNGParamFind = '$("'.$strFPNGParamFind.'")';

		//$strFPNGParamFind = str_replace("\'",'"',$strFPNGParamFind);	
		$strImgBlankUrl= $strUrl.'js/ressources/blank.gif';
	}
	
	if($bFZEffect || $bFPNGEffect){
		$strNewHead = '
		<!--// added by images_tools plugin -->
		<script language="JavaScript">
				$(function(){
					var tabScriptsToLoad = new Array();
					(!$.fn.ifixpng) && tabScriptsToLoad.push("'.$strUrl.'js/jquery.ifixpng.js");
		';
		if($bFZEffect){		
			$strNewHead.='
					(!$.fn.fancyzoom) && tabScriptsToLoad.push("'.$strUrl.'js/jquery.fancyzoom.js");
					(!$.fn.shadow) && tabScriptsToLoad.push("'.$strUrl.'js/jquery.shadow.js");
			
					var fzeffect = function(){
						var fzoptions = $.extend({'.$strFZDefaultOptions.'},'.$strFZOptions.');
						'.$strFZParamFind.'.fancyzoom(fzoptions);
					};
			';
		}
		if($bFPNGEffect){
			$strNewHead .= '
				var pngeffect = function(){
					$.ifixpng("'.$strImgBlankUrl.'");
					'.$strFPNGParamFind.'.ifixpng();
				}
			';
		}
		$strNewHead .='
					var imagestoolseffects = function(){
		';
		$strNewHead.=($bFZEffect)?'				fzeffect();'."\n":'';
		$strNewHead.=($bFPNGEffect)?'				pngeffect();'."\n":'';
		$strNewHead .='						
					};
		';
		$strNewHead .='
					(tabScriptsToLoad.length > 0) && loadJS(tabScriptsToLoad, imagestoolseffects);
					(tabScriptsToLoad.length == 0) && imagestoolseffects();
				});
		</script>
		<!--// end added by images_tools plugin -->
		';	
		return str_ireplace('</head>',$strNewHead."\n</head>",$source);		
	}
	return $source;
}

/**
 * images_tools_change_image_src_to_autoresize
 * Change the source of the displayed page.
 * Search for img, if param width and height is set, change the image src to imagestoolsresize.php script.
 * 
 * @param unknown_type $source
 * @param unknown_type $smarty
 * @return unknown
 */
function images_tools_change_image_src_to_autoresize($source, &$smarty) {	
	$oPlugin = new PPluginDir(dirname(__FILE__));
	$oConfigFile = &$oPlugin->oConfig;
		
	if( $oConfigFile->getDirectParam("SERVER_RESIZE") !== "true" ) 
		return $source;
	
	$strUploadUrl = preg_quote(POFile::getPathUrl(MEDIAS_PATH),'/');
	$source = preg_replace(
		array('/(<img([^>]*)width="([^"]*)"([^>]*)height="([^"]*)"([^>]*)src="('.$strUploadUrl.'[^"]*)"([^>]*)>)/',
		'/(<img([^>]*)src="('.$strUploadUrl.'[^"]*)"([^>]*)style="width:[^0-9]*([0-9]*)px;\sheight:[^0-9]*([0-9]*)px;[^"]*"([^>]*)>)/'	
		),
		array('<img\2\4\6src="'.SITE_URL.'imagestoolsresize.php?img=\7&width=\3&height=\5"\8>',
			'<img\2src="'.SITE_URL.'imagestoolsresize.php?img=\3&width=\5&height=\6"\4\7>'
		),
	$source);
	
/*
	$source = preg_replace(
		'/(<img([^>]*)width="([^"]*)"([^>]*)height="([^"]*)"([^>]*)src="('.$strUploadUrl.'[^"]*)"([^>]*)>)/',
		'<img\2\4\6src="'.SITE_URL.'imagestoolsresize.php?src=\7&width=\3&height=\5"\8>',
	$source);
	//if modified with mouse fck set style and not width and height properties
	$source = preg_replace(
		'/(<img([^>]*)src="('.$strUploadUrl.'[^"]*)"([^>]*)style="width:[^0-9]*([0-9]*)px;\sheight:[^0-9]*([0-9]*)px;[^"]*"([^>]*)>)/',
		'<img\2src="'.SITE_URL.'imagestoolsresize.php?src=\3&width=\5&height=\6"\4\7>',
	$source);
	*/

	return $source;
}



?>