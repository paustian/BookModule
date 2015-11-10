{*  book_admin_doexport.tpl,v 1.4 2007/01/05 04:19:50 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="Export Chapter"}</h2>
<form class="form" action="{modurl modname="Book" type="Admin" func="exportchapter"}" method="post" enctype="application/x-www-form-urlencoded">
<div>
    <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
    <table>
		{section name=i loop=$books}
		<tr><td>
		<input type="radio" name="book" value="{$books[i].bid|}" {if $smarty.section.i.index == 0}checked>{else}>{/if}{$books[i].name|}
		</td>
		<td>
		<select name="chapter_{$books[i].bid|}">
			{html_options options=$chapters[i]}
		</select>
		</td></tr>
		{/section}
	</table>
           <input type="checkbox" name="inline" checked />{gt text="Inline figures to html"}
	<p><input name="submit" type="submit" value="{gt text="Export Book"}" /></p>
</form>
<h2>{gt text="Export Whole Book"}</h2>
<form class="form" action="{modurl modname="Book" type="admin" func="exportbook"}" method="post" enctype="application/x-www-form-urlencoded">
	 <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
	<select name="book">
	{section name=i loop=$books}
		<option label="{$books[i].name|}" value="{$books[i].bid|}">{$books[i].name|}</option>
	{/section}
	</select>
	<p><input name="submit" type="submit" value="{gt text="Export Book"}" /></p>
</form>
	