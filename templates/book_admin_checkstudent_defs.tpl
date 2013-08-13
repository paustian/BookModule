{*  book_admin_checkstudent_defs.tpl,v 1.1 2007/02/03 13:02:44 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<form class="form" action="{pnmodurl modname="Book" type="admin" func="modifyglossaryitems"}" method="post" enctype="application/x-www-form-urlencoded">
<input type="hidden" name="gids" value="{$gids}" />
<table border=1>
<tr>
	<td>{gt text="Glossary ID"}</td>
	<td>{gt text="Glossary Term"}</td>
	<td>{gt text="Glossary Definition"}</td>
	<td>{gt text="Requesting user "}</td>
	<td>{gt text="Requesting url"}</td>
	<td>{gt text="Delete Definition"}</td>
</tr>
{section name=def loop=$empty_defs}
<tr>
	<td>{$empty_defs[def].gid}</td>
	<td><input type="text" name="term_{$empty_defs[def].gid}" value="{$empty_defs[def].term}" /></td>
	<td><textarea rows="3" cols="40" name="definition_{$empty_defs[def].gid}"></textarea></td>
	<td>{$empty_defs[def].user}</td>
	<td>{$empty_defs[def].url}</td>
	<td><input type="checkbox" name="delete_{$empty_defs[def].gid}" />
</tr>
{/section}
</table>
<p><input name="submit" type="submit" value="{gt text="Submit Definitions"}" /></p>

</form>