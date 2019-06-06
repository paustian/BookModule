{*  book_admin_modifyarticle1.tpl,v 1.2 2005/08/30 19:29:32 paustian Exp  *}
{include file="book_admin_menu.tpl"}

<h2>{gt text="Edit Article"}</h2>
<form class="form" action="{modurl modname="Book" type="admin" func="modifyarticle2"}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
    {$books}
    <p><input name="submit" type="submit" value="{gt text="Get Article"}" /></p>
</form>
