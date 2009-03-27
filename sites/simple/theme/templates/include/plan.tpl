{include file="include/top.tpl"}

{include file="include/menus/menuleft.tpl"}


<div class="guidage">
vous ête ici:  {page_guidage separator='>' homename='Accueil du site'}
</div>


<div id="blockContent">
	<div class="fixHeight">&nbsp;</div>
	{include file=$PAGE_CONTENU}
	<div id="sitemap">
		{insert name="menu"}
		<div style="clear:both">&nbsp;</div>
	</div>
</div>

{include file="include/bottom.tpl"}