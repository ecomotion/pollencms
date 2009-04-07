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
	$strReturn.= googleparsedir(getFileObject(PAGES_PATH));
		
	$strReturn .='
	</urlset>';
	return $strReturn;
}

function googleparsedir($oDir){
	$strReturn ='';
	$tabList = $oDir->listDir();
	foreach($tabList as $strPath){
		$oTemp = &getFileObject($strPath);
		if( $oTemp->isPublished() && strpos($oTemp->getName(),'404')===FALSE ){
			if(is_dir($oTemp->path)){
				$strReturn.=googleparsedir($oTemp);
			}else{
				$frequency = ($oTemp->isCached()?'monthly':'always');
				$strReturn .='<url>
					<loc>'.$oTemp->getUrl().'</loc>
						<lastmod>'.date('Y-m-d',filemtime($oTemp->path)).'</lastmod>
						<changefreq>'.$frequency.'</changefreq>
					</url>
				';
			}
		}
	}
	return $strReturn;
}




?>