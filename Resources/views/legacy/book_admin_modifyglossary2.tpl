{*  book_admin_modifyglossary2.tpl,v 1.3 2007/02/03 13:02:44 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="Edit Glossary"}</h2>
<form class="form" action="{modurl modname="book" type="admin" func="updateglossary"}" method="post" enctype="application/x-www-form-urlencoded">
<input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
<p>{gt text="Glossary ID"}{$glossary.gid}<input type="hidden" name="gid" value="{$glossary.gid}"></p>
<table>
<tr>
	<td>{gt text="Glossary Term"}</td>
	<td>
  		<input type="text" name="term" size="40" value="{$glossary.term}" maxlength="256"/>
	</td>
</tr>
<tr>
	<td>{gt text="Glossary Definition"}</td>
	<td>
  <textarea name="definition" rows="10" cols="80" wrap="virutal">{$glossary.definition}</textarea>
  </td>
</tr>
</table>
<input name="submit" type="submit" value="{gt text="Edit Glossary"}" />
</form>