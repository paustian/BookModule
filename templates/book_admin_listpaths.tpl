
{include file="book_admin_menu.tpl"}
<h2>{gt text="List Figures"}</h2>
<form class="form" action="{pnmodurl modname="Book" type="admin" func="modifyimagepaths"}" method="post" enctype="application/x-www-form-urlencoded">
<input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
<table>
{section name=i loop=$figData}
<tr>
	<td><input type="checkbox" name="fid[]" value="{$figData[i.index].fid}"></td>
	<td>{$figData[i.index].chap_number}-{$figData[i.index].fig_number} {$figData[i.index].title}</td>
	<td><input type="text" name="img_link[{$figData[i.index].fid}]" size="70" value="{$figData[i.index].img_link}"></td>
</tr>
{/section}
</table>
<p><input name="submit" type="submit" value="{gt text="Edit Figure Paths"}" /></p>

</form>