{*  book_admin_verify_chapter.tpl,v 1.1 2006/12/23 22:59:01 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="Verify Chapters"}</h2>
<p>{gt text="This function will check all urls in a chapter an make sure the links are working. Just choose the chapter you want to check and hit Check urls. Note, depending upon the speed of your network and the web servers you link to, this could take time."}</p>
<form class="form" action="{pnmodurl modname="Book" type="admin" func="verify_urls"}" method="post" enctype="application/x-www-form-urlencoded">
<div>
    <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
    <table>
		{section name=i loop=$books}
		<tr><td>
		<input type="radio" name="bid" value="{$books[i].bid|pnvarcensor}" {if $smarty.section.i.index == 0}checked>{else}>{/if}{$books[i].name|pnvarcensor}
		</td>
		<td>
		<select name="chapter_{$books[i].bid|pnvarcensor}">
			{html_options options=$chapters[i]}
		</select>
		</td></tr>
		{/section}
	</table>
	<p><input name="submit" type="submit" value="{gt text="Check urls"}" /></p>
</form>