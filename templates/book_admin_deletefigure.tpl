{*  book_admin_deletefigure.tpl,v 1.2 2005/08/30 19:29:32 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="Delete Figure"}</h2>
<form class="form" action="{modurl modname="Book" type="admin" func="deletefigure"}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
    <p>{gt text="Choose a figure to delete"}
    <p>{gt text="Last 50 Figures"}</p>
    <select name="fid1">
    {html_options options=$fig_list}
    </select>
    
    <p>{gt text="Or enter the Figure ID: "}<input type="text" name="fid2" size="3" maxlength="9"/></p>
	<p><input name="submit" type="submit" value="{gt text="Delete Figure"}" /></p>
</form>