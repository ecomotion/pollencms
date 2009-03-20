{php}
$strTplFilePath = CONFIG_DIR.'fckeditor'.SLASH.'fckstyles.xml';
if(is_file($strTplFilePath)){
	$content .= file_get_contents($strTplFilePath);
	echo $content;
}
{/php}
