{include file="include/top.tpl"}


{include file="include/menus/menuleft.tpl"}


<div class="guidage">
vous ête ici:  {page_guidage separator='>' homename='Accueil du site'}
</div>


<div id="blockContent">
	
	
	<div class="fixHeight">&nbsp;</div>
	
	{if isset($smarty.get.action) &&  $smarty.get.action eq 'send' }
		{include file='snipplets/mail.tpl'}
	{else}
		{include file='snipplets/form.tpl'}
	{/if}

</div>

{include file="include/bottom.tpl"}