{*  book_admin_modifyconfig.tpl,v 1.1 2007/01/01 17:26:25 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<hr />
<form action="{pnmodurl modname="book" type="admin" func="modifyaccess"}" method="post" enctype="application/x-www-form-urlencoded">
  <p><input name="secure" type="checkbox" value="makesecure" {$issecure}> Prevent simultaneous access of two or more people using the same user name</p>
 <input type="submit">
</form>