{*  book_admin_newbook.tpl,v 1.3 2006/12/23 22:59:01 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="Add a New Book"}</h2>
<form class="form" action="{modurl modname="book" type="admin" func="create"}" method="post" enctype="application/x-www-form-urlencoded">
<div>
	<input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
	<div class="formrow">
		<label for="name">{gt text="Book Name"}</label>
		<input id="name" name="name" type="text" size="32" maxlength="256" />
	</div>
	<div class="formrow">
		<input name="submit" type="submit" value="{gt text="Add Book"}" />
	</div>
</div>
</form>