<?php

addPollenPlugin('adminmainmenu','admin_btn_news');
addPollenPlugin('extrapage','extrapage_rss_feed');
addPollenPlugin('getpagecontent','news_content');
addSmartyPlugin('insert','insert_newsrss','news_rss');
addSmartyPlugin('insert','tplfct_news_rss','rss_page');

/**
 * tplfct_news_rss
 * This is the template function rss_page use by the get feed template rss_page
 *
 * @param array $params, parameters from the template
 * @param Pollen CMS object $smarty
 * @return the feed in xml
 */
function tplfct_news_rss($params, &$smarty)
{
	$strTplChannel='<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
	<channel>
		<title>%CHANNEL_TITLE%</title>
		<link>%CHANNEL_LINK%</link>
		<description>%CHANNEL_DESCRIPTION%</description>
		<pubDate>%CHANNEL_PUBDATE%</pubDate>
		<generator>%CHANNEL_GENERATOR%</generator>
		%ITEMS%
	</channel>
</rss>
';
	
	$strServerUrl = strstr(SITE_URL,'http')?'':'http://'.$_SERVER['SERVER_NAME'].'';
	$tabInfosChannel = array(
		'title'=>'test',
		'link'=>$strServerUrl.SITE_URL,
		'description'=>'ceci est la description',
		'pubDate'=>gmdate('D, d M Y H:i:s',time()).' GMT',
		'generator'=>'POLLEN CMS'
	);
	
	$keys = array_keys($tabInfosChannel);
	$tabKeysTransform = array();
	$tabKeysValues = array();
	foreach($keys as $aKey){
		$tabKeysTransform[] = '%CHANNEL_'.strtoupper($aKey).'%';
		$tabKeysValues[] = $tabInfosChannel[$aKey];
	}
	$strReturn = str_replace($tabKeysTransform,$tabKeysValues,$strTplChannel);
	
	$strItemTpl ='
		<item>
			<title>%ITEM_TITLE%</title>
			<link>'.$strServerUrl.'%ITEM_LINK%</link>
			<pubDate>%ITEM_PUBDATE%</pubDate>
			<description>%ITEM_DESCRIPTION%</description>
		</item>
	';
	$strListItems ='';
	$tabItems = array();
	$pdirNews = &getFileObject(PAGES_PATH.SLASH.'actualites');
	if(!$pdirNews || !is_dir($pdirNews->path) ){
		setError('Error can not find the new directory');
		printFatalHtmlError();	
	}
	$tabNews = $pdirNews->listDir($pdirNews->ONLY_FILES);
	foreach($tabNews as $strNews){
		$oNews = &getFileObject($strNews);
		$tabItems[]=array(
			'%ITEM_TITLE%'=>$oNews->getMenuName(),
			'%ITEM_LINK%'=>$oNews->getUrl(),
			'%ITEM_PUBDATE%'=>gmdate('D, d M Y H:i:s',filemtime($oNews->path)).' GMT',
			'%ITEM_DESCRIPTION%'=>_getNewsContent($oNews,$strServerUrl)
		);
	}
	foreach($tabItems as $aItem){
		$keys = array_keys($aItem);
		$values = array_values($aItem);
		$strListItems .= str_replace($keys,$values,$strItemTpl);
	}
	
	$strReturn = str_replace('%ITEMS%',$strListItems,$strReturn);
	return $strReturn;
}

function _getNewsContent(&$oNews,$strServerUrl){
	$strContent = $oNews->getEditorFileContent();
	if($iPos = strpos($strContent, '....')){
		$strContent = substr($strContent,0,$iPos);
	}
	//$strContent = str_replace('{$MEDIAS_URL}',MEDIA_URL,$strContent);
	$strContent = preg_replace('/<h[0-9]>[^<]*<\/h[0-9]>/','',$strContent);
	$strContent = str_replace('{#SITE_URL#}',$strServerUrl.SITE_URL,$strContent);
	
	
	return $strContent;
}

function extrapage_rss_feed($strPage, &$site) {
	if($strPage == 'feed.xml'){	
		$strTplFile = dirname(__FILE__).SLASH.'rss_page.tpl';
		if( !$site->template_exists($strTplFile) ){
			setError('Error in plugin newsrss. Can not find the rss_page.tpl file.');
			printFatalHtmlError();
			die();
		}
		header ('Content-Type: text/xml;');	
		$site->display($strTplFile);
		return true;
	}
	return false;
}

function insert_newsrss($params, &$smarty)
{
	
	$strFeedUrl = isset($params['FEED_URL'])?$params['FEED_URL']:false;
	if(!$strFeedUrl) return '';
	$iFeedCacheTime = isset($params['FEED_CACHETIME'])?intval($params['FEED_CACHETIME']):3600;
	$strFeedDateFormat = isset($params['FEED_DATEFORMAT'])?$params['FEED_DATEFORMAT']:'d / m / Y';
	$iFeedItems = isset($params['FEED_NBITEMS'])?intval($params['FEED_NBITEMS']):0;
	//check that the dir news exists
	require_once dirname(__FILE__).SLASH.'vendors'.SLASH.'lastrss.php';
	$strNewsTpl ='
		<div class="news">
			<h2>%TITLE%</h2>
			<h3>%PUBDATE%</h3>
			<p>%DESCRIPTION%</p>
			<p><a class="readmore" href="%LINK%">En savoir plus</a></p>
		</div>
	';
	$strReturn = '';
	$oRSS = new lastRSS();
	$oRSS->stripHTML=false;
	$oRSS->CDATA='content';
	$oRSS->cache_dir = CACHE_DIR.'rss';
	$oRSS->cache_time = $iFeedCacheTime;
	$oRSS->date_format=$strFeedDateFormat;
	$oRSS->items_limit=$iFeedItems;
	
	if(!is_dir($oRSS->cache_dir)){
		mkdir($oRSS->cache_dir);
	}
	if(!$rs = $oRSS->get($strFeedUrl))
		return 'Error while getting rss feed';

		
	$i=0;
	foreach($rs['items'] as $aNews){
		
		$strNews = str_replace(
			array('%TITLE%','%PUBDATE%','%DESCRIPTION%','%LINK%'),
			array($aNews['title'],$aNews['pubDate'],$aNews['description'],$aNews['link']),
			$strNewsTpl);
		if($rs['encoding'] != 'UTF-8')
			$strNews = utf8_encode($strNews);
		$strReturn .=htmlspecialchars_decode($strNews);
		$i++;
		if($i==$iFeedItems)
			break;
	}
	return "\n".$strReturn."\n";

}

function admin_btn_news() {	
	return '
	<a href="admin_file_management.php?current_dir='.urlencode(POFile::getPathRelativePath(PAGES_PATH.'/actualites/')).'"  class="panel-link infobulles" id="actualites">
		<img src="'.SITE_URL.'plugins/news/img/news.jpg" />
			<span>
				<h3> Gestion des actualités </h3>
				<p>Dans cette espace vous pouvez gérer vos actualités.</p>
			</span>
	</a>
		
	';
}

	
		
	/**
	 * fancyimageresize allow to intercept the fancyimageresize page.
	 * It return the image resized.
	 *
	 * @param unknown_type $strPage
	 * @param unknown_type $site
	 * @return unknown
	 */
	function news_content(&$strSource, &$params, &$site) {
		require (SITE_PATH.'core/lib/pimage.php');	
		$strMoreSeparator = isset($params['MORE_SEPARATOR'])?$params['MORE_SEPARATOR']:'....';
		if($strMoreSeparator){
			$strSource = str_replace('<br />....','',$strSource);
			$strSource = str_replace('....','',$strSource);
		}
		return true;
	}	

?>