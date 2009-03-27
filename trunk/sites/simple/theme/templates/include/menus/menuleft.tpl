<div id="menuLeft">

	<form action="{#SITE_URL#}recherche.html" method="get">
		<input type="text" name="search" value="{$smarty.get.search|default:Rechercher}" >
	</form>

	{insert name="menu" LEVEL_START=1 EXPAND=true}

</div>

