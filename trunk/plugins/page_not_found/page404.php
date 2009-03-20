<?php
addPollenPlugin('extrapage','page404',1);

function page404($strPage, &$site)
{
	if( !($oPage = &$site->getPage('404.html')) )
		return false;

	header("HTTP/1.0 404 Not Found");
	return $oPage;
}
?>