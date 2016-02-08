{*  book_admin_verify_urls.tpl,v 1.1 2006/12/23 22:59:01 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="Chapter"} - {$chapter_num}</h2>
<h3>{gt text="Bad Links"}</h3>
<table border="1">
<tr><th>{gt text="Chapter"}</th><th>{gt text="Article Number"}</th><th>{gt text="url"}</th></tr>
{foreach item=url from=$url_table}
{if $url.present == -1}
<tr>	<td>{$url.chap_no}</td>
	<td>{$url.article_no}</td>
	<td>{$url.url}</td>
</tr>
{/if}
{/foreach}
</table>
<h3>{gt text="Good Links"}</h3>
<table border="1">
<tr><th>{gt text="Chapter"}</th><th>{gt text="Article Number"}</th><th>{gt text="url"}</th></tr>
{foreach item=url from=$url_table}
{if $url.present == 1}
<tr>	<td>{$url.chap_no}</td>
	<td>{$url.article_no}</td>
	<td>{$url.url}</td>
</tr>
{/if}
{/foreach}
</table>
