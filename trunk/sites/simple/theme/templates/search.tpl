{include file="include/top.tpl"}

{include file="include/menus/menuleft.tpl"}


<div class="guidage">
vous ête ici:  {page_guidage separator='>' homename='Accueil du site'}
</div>


<div id="blockContent">
	
	
	<div class="fixHeight">&nbsp;</div>
		{include file=$PAGE_CONTENU}

		{search keywords=$smarty.get.search modifytext=true modifyscore=true}
		
		<div id="contentSearch">
		
		<p><strong>Résultat de la recherche pour : <span class="blueLight">{$smarty.get.search}</span></strong><br />
		{$RESULT_SEARCH_SIZE} résultat(s) trouvé(s)</p>
		
		{section name=result loop=$RESULT_SEARCH}
		
			<div class="block_search" id="paginate">
		   
				<h4> {$RESULT_SEARCH[result].nom}  - {$RESULT_SEARCH[result].score} % </h4>
				<p> {$RESULT_SEARCH[result].texte} <br />
				<a href="{#SITE_URL#}{$RESULT_SEARCH[result].url}"><em>{$RESULT_SEARCH[result].url|default:#SITE_URL#}</em></a> </p>
						
			</div>
		
		{/section}
		
		<div id="mypaginate"></div>
		
		</div>

</div>

<script language='JavaScript'>
$(function(){ldelim}

	$('#contentSearch').paginate({ldelim}
		nbElemsPerPage:4, 
		items:'.block_search', 
		strPrev:'précédent', 
		strNext:'suivant',
		classSelected:'selected',
		paginateBox:$("#mypaginate"),
		nbPagesMax:4
	{rdelim});

{rdelim});
</script>


{include file="include/bottom.tpl"}

