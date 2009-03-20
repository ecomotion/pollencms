{mySite->getSiteMap assign="tabMap"}

{section name=elem1 loop=$tabMap}
	<div class="map">
		<h2><a href="{$tabMap[elem1].URL}">{$tabMap[elem1].NAME}</a></h2>

		{assign var=tabLevel2 value=`$tabMap[elem1].LEVEL2`}
		{section name=elem2 loop=$tabLevel2}
			<h3><a href="{$tabLevel2[elem2].URL}">{$tabLevel2[elem2].NAME}</a></h3>

			{assign var=tabLevel3 value=`$tabLevel2[elem2].LEVEL3`}
			{section name=elem3 loop=$tabLevel3}
				{if $smarty.section.elem3.first}
					<ul>
				{/if}
					<li><a href="$tabLevel3[elem3].URL">{$tabLevel3[elem3].NAME}</a></li>
				{if $smarty.section.elem3.last}
					</ul>
				{/if}
			{/section}
		{/section}
	</div>
{/section}
