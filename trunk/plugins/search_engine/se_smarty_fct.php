<?php
addSmartyPlugin('insert','se_search','search');
addSmartyPlugin('output','add_paginate');


function add_paginate($source, &$smarty)
{
	$oDir = new PDirCategory(dirname(__FILE__));
	$strUrl = $oDir->getUrl();
	
	$strNewHead= '<!-- add by plugin search engine to paginates the results -->
		<script type="text/javascript" src="'.$strUrl.'include/jquery.paginate.js" ></script>
	';
	return str_replace('</head>',$strNewHead."\n</head>",$source);
}

function se_search($params, &$smarty) {	
	
	$keyword = isset($params['keywords'])?$params['keywords']:'null';
	$modifytext = isset($params['modifytext'])?$params['modifytext']:true;
	$modifyscore = isset($params['modifyscore'])?$params['modifyscore']:true;
	
	/* library */
	require( dirname(__FILE__).SLASH.'include/lib.searchengine.php' );
	
	$result_search_engine = query_search_engine($keyword);
	if($result_search_engine === false){
		printError();return;
	}
	
		
	$iScoreMax=$result_search_engine[0]['score'];
	foreach($result_search_engine as &$elem){
		
		if($modifyscore){
			$pourcent = intval($elem['score']*100/$iScoreMax);
			$elem['score']=$pourcent;
		}
	
		if($modifytext){
			$elem['texte']=substr(strongText($elem['texte'], $keyword),0,400).' ...';
			$elem['nom']=strongText($elem['nom'], $keyword);
		}
	}
	$smarty->assign('RESULT_SEARCH_SIZE', sizeof($result_search_engine));
	$smarty->assign('RESULT_SEARCH',$result_search_engine);
	//return true;
}
?>