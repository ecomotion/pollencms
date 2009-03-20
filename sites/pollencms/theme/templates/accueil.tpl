{include file="include/top.tpl"}

{include file="include/menus/menutop.tpl"}

<div id="blockContent">

	<div id="listNews">

		{*insert name="newsrss" FEED_URL="http://feeds.macbidouille.com/macbidouille/" FEED_NBITEMS=2*}
		{news_rss FEED_URL="http://www.smec-expertise.com/test/feed.xml" FEED_NBITEMS=2 FEED_CACHETIME=0}
		{news_rss FEED_URL="http://localhost/~mathieuvilaplana/pollencms/feed.xml" FEED_NBITEMS=2 FEED_CACHETIME=0}
		{news_rss FEED_URL="http://feeds.feedburner.com/WordpressFrancophone" FEED_NBITEMS=2}

		{*insert name="news" DIR_NEWS="actualites"  NONEWS_MSG="Pas d'actualités" MORE_MSG="lire la suite" NEWS_WRAPPER="div" NEWS_WRAPPER_CLASS="news"*}
	</div>
	<div class="fixHeight">&nbsp;</div>
		{pagecontent}
</div>

{include file="include/bottom.tpl"}