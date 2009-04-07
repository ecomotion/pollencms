{include file="include/top.tpl"}


{include file="include/menus/menuleft.tpl"}


<div class="guidage">
vous ête ici:  {page_guidage separator='>' homename='Accueil du site'}
</div>


<div id="blockContent">
	
	
	<div class="fixHeight">&nbsp;</div>
		{*news_rss FEED_URL="http://feeds.macbidouille.com/macbidouille/" FEED_NBITEMS="3"*}
		{*news_rss FEED_URL="http://rss.feedsportal.com/c/853/f/10952/index.rss" FEED_NBITEMS="3"*}
		
		{pagecontent}

</div>

{include file="include/bottom.tpl"}