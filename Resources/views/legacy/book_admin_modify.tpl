{*  book_admin_modify.tpl,v 1.4 2006/12/23 22:59:01 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="Edit Book"}</h2>
<form class="form" action="{modurl modname="Book" type="admin" func="update"}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
    <table border>
     <tr><td>{gt text="Edit Book"}</td><td>{gt text="Book Name"}</td></tr>
    {section name=i loop=$books}
   <tr><td>
    <input type="radio" name="bid" value="{$books[i].bid}" {if $smarty.section.i.index == 0}checked>{else}>{/if}{$books[i].bid}</td><td><input type="text" size="50" max="256" name="{$books[i].bid}" value="{$books[i].name}"></td></tr>
	{/section}
	</table>
	<p><input name="submit" type="submit" value="{gt text="Update Book"}" /></p>
</form>
