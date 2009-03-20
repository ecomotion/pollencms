<?php
addPollenPlugin('extrapage','extrapage_sitemap');
addSmartyPlugin('insert','google_sitemap_fct','google_sitemap');

/**
 * extrapage_sitemap
 * This is the extrapagesite map event function
 * This function check the name of the page and if it is sitemap.xml, generate the site map.
 *
 * @param unknown_type $strPage
 * @param unknown_type $site
 * @return unknown
 */
function extrapage_sitemap($strPage, &$site)
{
	if($strPage == 'sitemap.xml'){
		$strTplFile = dirname(__FILE__).SLASH.'google_sitemap.tpl';
		if( !$site->template_exists($strTplFile) ){
			setError('Error in plugin google site map. Can not find the google_sitemap.tpl file.');
			printFatalHtmlError();
			die();
		}
		header ('Content-Type: text/xml;');
		$site->display($strTplFile);
		return true;
	}
	return false;
}

/**
 * google_sitemap_fct
 * This is the smarty function used in the google_sitemap.tpl.
 * This fonction generate the xml output of the google site map
 *
 * @param smarty parameters $params
 * @param the PollenCMS $smarty object
 * @return the xml string of the google site map
 */
function google_sitemap_fct($params, &$smarty)
{
	$strReturn.='<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">
	';
	
	$strReturn .= __getUrlMenu($smarty, $smarty->getMenu());
	
	$strReturn .='
	</urlset>';
	return $strReturn;
}

function __getUrlMenu(&$smarty,$tabMenu){
	
	$url = preg_match('/http:/',SITE_URL)?SITE_URL:'http://'.$_SERVER['HTTP_HOST'].SITE_URL;
	$strReturn='';
	foreach($tabMenu as $menuElem){
		//relative url
		$shortUrl = substr(rawurldecode($menuElem['URL']),strlen(SITE_URL));
		$page=&getFileObjectAndFind($menuElem['PATH']);

		if(file_exists($page->path)){
			if(isset($menuElem['SUBMENU']) && sizeof($menuElem['SUBMENU'])>0){
				$strReturn.=__getUrlMenu($smarty, $menuElem['SUBMENU']);
			}else if(is_file($page->path)){
				$frequency = ($page->isCached()?'monthly':'always');
				$strReturn.= '<url>
					<loc>'.$url.$shortUrl.'</loc>
					<lastmod>'.date('Y-m-d',filemtime($page->path)).'</lastmod>
					<changefreq>'.$frequency.'</changefreq>
				</url>
				';
			}
		}
	}
	return $strReturn;
}


?>