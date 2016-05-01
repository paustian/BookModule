{*  book_user_search.tpl,v 1.1 2006/12/23 22:59:01 paustian Exp  *}
Items Found: {$total}<br />
{section name=i loop=$art_urls max=$pager.itemsperpage}
{$art_urls[i]}<br />
{/section}
{pager show=page rowcount=$pager.numitems limit=$pager.itemsperpage posvar=startnum shift=1}