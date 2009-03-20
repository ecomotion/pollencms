<?php
if(!defined("PLUGIN_SEARCHBASE")){
define("PLUGIN_SEARCHBASE",1);

/* DFC - Tibo - 2008 */
/* Enregistre la page courante dans la base de données */
$connexion_search=false;
$seConfigFile = null;

function se_get_config($strParam,&$strValue){
	global $seConfigFile;
	if(!$seConfigFile){
		$oDir = new PDir(dirname(__FILE__));
		$oPlugin = new PPluginDir($oDir->getParentPath());
		$seConfigFile = $oPlugin->oConfig;
	}
	
	if($strParam=='tablename'){
		if(!$seConfigFile->getParam('PREFIX',$strPrefix))
			return false;
		$strValue =  $strPrefix.'searchengine';
	}
	else if(!$seConfigFile->getParam($strParam,$strValue)){
		return false;
	}
	if($strValue === "true")
		$strValue = true;
	if($strValue === "false")
		$strValue = false;
	return true;	
}

function connectBdd(){
	global $connexion_search;
	if($connexion_search) return $connexion_search;

	if(!se_get_config('HOST',$host))
		return false;
	if(!se_get_config('LOGIN',$login))
		return false;
	if(!se_get_config('PASSWD',$passwd))
		return false;
	if(!se_get_config('BASE',$base))
		return false;

	$connexion_search=@mysql_connect($host,$login,$passwd);
	if(!$connexion_search)
		return setError(sprintf(_('can not connect to data base on server %s'),$host).' '.mysql_error());
	$db = @mysql_select_db($base, $connexion_search);
	if(!$db)
		return setError(sprintf(_('can not connect to data base %s'),$base).' '.mysql_error());

	return $connexion_search;
}


function closeBdd($error=false){
	global $connexion_search;
	mysql_close($connexion_search);
	$connexion_search=false;
	
	return (!$error)?true:setError($error);
}


function cleanUrl($url) {
	return preg_replace('/^'.preg_quote(SITE_URL,'/').'/','',$url);
}


function cleanText($texte) {
	return stripTagPhpHtml($texte);
}


function stripTagPhpHtml($texte) {
	//return strip_tags($texte); // attention supprime des espaces et colle des mots
	return preg_replace ('@<[\/\!]*?[^<>]*?>@si', ' ', $texte);
}


function exist_page_search($url) {
	if(!se_get_config('tablename',$strTable))
		return false;

	$url = cleanUrl($url);
	$sql = "SELECT COUNT(*) FROM $strTable WHERE url = '$url'";
	$rsql = mysql_query($sql);
	$resultat = mysql_fetch_array($rsql);
	if ( $resultat[0] >= 1 ) { return true; }
	else { return false; }
}

function insert_info_search(&$oPage, $texte=false) {
	
	/* init value and bdd */
	if($texte == false){
		$texte =file_get_contents($oPage->path);
	}
	$dateModif = date("d-m-Y");
	$dateCrea = date("d-m-Y");
	//$name = $oPage->getShortName();
	$name = $oPage->getMenuName();
	$url = cleanUrl($oPage->getUrl());
	$texte = addslashes($texte);
	$texteClean = cleanText($texte);
	$published=$oPage->isPublished();

	if(!se_get_config('tablename',$strTable))
		return false;
	
	$sql = "INSERT INTO $strTable (nom , url, texte , texte_html , date_crea , date_modif, published ) 
	VALUES ( '$name', '$url', '$texteClean', '$texte', '$dateCrea', '$dateModif', '$published' )";
	
	/* save value */
	if (!mysql_query($sql))
		return setError(sprintf(_('Mysql Error occured : %s'),mysql_error()));
	return true;
}


function update_info_search(&$oPage, $texte) {
	
	/* init value and bdd */
	$dateModif = date("d-m-Y");
	$url = cleanUrl($oPage->getUrl());
	$texte = addslashes($texte);
	$texteClean = cleanText($texte);
	$name = $oPage->getMenuName();
	$published=$oPage->isPublished();
	
	if(!se_get_config('tablename',$strTable))
		return false;

	/* ne fait rien si il n'existe pas */
	$sql = "UPDATE $strTable SET nom = '$name', texte = '$texteClean' , texte_html = '$texte' , date_modif = '$dateModif', published = '$published' WHERE url = '$url'";
	
	/* save value */
	if (!mysql_query($sql))
		return setError(sprintf(_('Mysql Error occured : %s'),mysql_error()));
	return true;
}


function rename_info_search(&$oPage, &$strNewPageName) {
	if(!se_get_config('tablename',$strTable))
		return false;

	if(!connectBdd())
		return false;
	/* init value and bdd */
	$dateModif = date("d-m-Y");
	$oPageNew = new PPage($strNewPageName);
	$newname = addslashes($oPageNew->getMenuName());
	$url = cleanUrl($oPage->getUrl());
	$newUrl = cleanUrl($oPageNew->getUrl());
	$published=$oPage->isPublished();


	$sql = "UPDATE $strTable SET nom = '$newname' , url = '$newUrl' , date_modif = '$dateModif', published = '$published' WHERE url = '$url'";
	
	/* save value */
	if (!mysql_query($sql))
		return closeBdd(sprintf(_('Mysql Error occured : %s'),mysql_error()));

	return closeBdd();
}


function delete_info_search(&$oPage) {
	if(!se_get_config('tablename',$strTable))
		return false;

	if(!connectBdd())
		return false;

	/* delete */
	$url = cleanUrl($oPage->getUrl());
	$sql = "DELETE FROM $strTable WHERE url = '$url'";

	/* save value */
	if (!mysql_query($sql))
		return closeBdd(sprintf(_('Mysql Error occured : %s'),mysql_error()));
	return closeBdd();
}


function createBase(){
	
	if(!se_get_config('tablename',$strTableName))
		return false;
	if(!se_get_config('BASE',$strBase))
		return false;
		
	//on essai de trouver la table et si elle existe on la supprime
	if(!($con = connectBdd()))
		return false;
	if(! ($result =@mysql_query('SHOW TABLES FROM '.mysql_real_escape_string($strBase))) )
		return setError(_('can not list database tables').' '.$strBase.' '.mysql_error());
	
	$bTableExists = false;
	while (($row = mysql_fetch_assoc($result)) && !$bTableExists ){
		foreach($row as $k => $v){
			if($v === $strTableName){
				$bTableExists = true;
			}
		}
	}
	//La table exists, on la suppprime
	if( $bTableExists && !($result = mysql_query('DROP TABLE '.mysql_real_escape_string($strTableName))) )
		return closeBdd(sprintf(_('can not delete table %s'),$strTableName).' '.mysql_error());
	
	//create the database
	$strSQL = file_get_contents('base.sql');
	if(!$strSQL)
		return closeBdd(_('can not load the sql template file'));

	$strSQL = str_replace('%TABLE_NAME%',$strTableName,$strSQL);
	if( !($result = mysql_query($strSQL)) )
		return closeBdd(sprintf(_('can not create table %s'),$strTableName).' '.mysql_error());
	
	return closeBdd();
}

function synchroBase() {

	if(!se_get_config('tablename',$strTable))
		return false;
	
	if(!connectBdd())
		return false;
	
	if(!mysql_query("TRUNCATE TABLE $strTable"))
		return closeBdd(mysql_error());
		
	/* synchronisation base - contenu actuel */
	$oDir = &getFileObject(PAGES_PATH);
	$bStatus = syncDir($oDir);

	closeBdd();
	
	return $bStatus;
	
}

function syncDir(&$pDir){
	$tabList = $pDir->listDir();
	foreach($tabList as $strPath){
		if(is_dir($strPath)){
			if(!syncDir(getFileObject($strPath)))
				return false;
		}
		else {
			if(!insert_info_search(getFileObject($strPath)))
				return false;			
		}
	}
	return true;
}


function strongText($texte, $keyword){
	$tab = split(' ', $keyword);
	foreach ($tab as $value) {
    	$replacements = "<strong>$1</strong>";
    	$texte = preg_replace('/('.preg_quote(htmlentities($value,ENT_COMPAT,'UTF-8'),'/').')/i', $replacements, $texte);
	}
	return $texte;
}

function query_search_engine($keyword) {
	if(!se_get_config('tablename',$strTable))
		return false;

	if(!$cnx=connectBdd())
		return false;
		
	$tabKeyword=split(' ',$keyword);
	$strK ='';
	foreach($tabKeyword as $k) {
		$strK.=$k.'.*';
	}
	$strPublishedCondition='';
	if(!isConnected())
		$strPublishedCondition =' AND published=1 ';
	$sql = " SELECT *, MATCH(nom, url, texte) AGAINST('$strK') AS score 
	FROM $strTable WHERE MATCH(nom, url, texte) AGAINST('$keyword') $strPublishedCondition
	ORDER BY score DESC ";

	if( !($rest = mysql_query($sql, $cnx)) )
    	return closeBdd(mysql_error($cnx));
    
    $result_search_engine=array();
    while($row = mysql_fetch_array($rest)) {
		$result_search_engine[]=$row;
	}
	closeBdd();
	return $result_search_engine;
}


/* scoring table */
function scoreTab() {
	$tab = array (
		/* titre */
		'h1' => '10',
		'h2' => '9',
		'h3' => '8',
		'h4' => '7',
		'h5' => '4',
		'h6' => '1',
		
		/* block */
		'p' => '1',
		'div' => '1',
		'span' => '1',
		'acronym' => '1',
		'blockquote' => '1', 
		
		/* highlight */
		'b' => '6',
		'big' => '6',
		'strong' => '6',
		'i' => '4',
		'em' => '1',
		'small' => '1',
		
		/* output */
		'pre' => '1',
		'code' => '1',
		
		/* links */
		'a' => '2',
		'link' => '2',
		
		/* Input - Form */
		'form' => '1',
		'input' => '2',
		'textarea' => '1',
		'button' => '2',
		'select' => '2',
		'optgroup' => '2',
		'option' => '1',
		'label' => '2',
		'fieldset' => '1',
		'legend' => '2',
		
		/* list */
		'ul' => '4',
		'ol' => '2',
		'li' => '2',
		'dl' => '4',
		'dt' => '2',
		'dd' => '2',
		
		/* other */
		'img' => '4',
		
		/* table */
		'table' => '2',
		'caption' => '2',
		'th' => '2',
		'td' => '2',
		'tr' => '4',
		
	);
}

}

?>