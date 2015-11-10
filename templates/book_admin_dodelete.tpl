{*  book_admin_dodelete.tpl,v 1.4 2006/12/23 22:59:01 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="Delete Book"}</h2>
<h2>{gt text="Warning deleting a book will remove all its chapters and its articles. Make sure that this is what you want to do before hitting submit."}</h2>
<form class="form" action="{modurl modname="Book" type="admin" func="delete"}" method="post" enctype="application/x-www-form-urlencoded">
<div>
    <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
    {section name=i loop=$books}
    <input type="radio" name="bid" value="{$books[i].bid|}" {if $smarty.section.i.index == 0}checked>{else}>{/if}{$books[i].bid|} - {$books[i].name|}
	<br/>
	{/section}
	<p><input name="submit" type="submit" value="{gt text="Delete Book"}" /></p>
</form>
