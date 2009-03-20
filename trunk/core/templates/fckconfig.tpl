{php}
$strTplFilePath = CONFIG_DIR.'fckeditor'.SLASH.'fckconfig.js';
if(is_file($strTplFilePath)){
	global $configFile;
	$content='
		FCKConfig.BodyId = \''.$configFile->getDirectParam('FCK_BODYID').'\';
	';
	$content .= file_get_contents($strTplFilePath);
	echo $content;
}
{/php}
