{php}
echo '<?xml version="1.0" encoding="utf-8" ?>';
$strTplFilePath = CONFIG_DIR.'fckeditor'.SLASH.'fcktemplates.xml';
if(is_file($strTplFilePath)){
	$oFile = new PFile($strTplFilePath);
	$strBasePath = SITE_URL.$oFile->getParentDir()->getRelativePath().'/';
	$content = file_get_contents($strTplFilePath);
	$content = preg_replace('/imagesBasePath="([a-z_\/]*)"/i','imagesBasePath="'.$strBasePath.'\1"',$content);
	echo $content;
}
{/php}

