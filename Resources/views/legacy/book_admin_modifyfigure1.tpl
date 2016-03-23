{*  book_admin_modifyfigure1.tpl,v 1.3 2006/12/23 22:59:01 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="Edit Figure"}</h2>
<form class="form" action="{modurl modname="Book" type="admin" func="modifyfigure2"}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
    <p>{gt text="Last 50 figures"}
    <select name="fid1">
    {html_options options=$fig_list}
    </select></p>
    <hr />
    <p>{gt text="Or enter the Figure ID: "}<input type="text" name="fid2" size="3" maxlength="9"/></p>
	<hr />
	<p>{gt text="Or choose the book, the chapter and figure number"}</p>
	<br />{gt text="Book: "}<select name="bid">
			{html_options options=$book_menu}
			</select>
	<br />
	{gt text="Chapter Number:"}<input name="chap_number" type="text" size="3" maxlength="3">
	<br />
	{gt text="Figure Number:"}<input name="fig_number" type="text" size="3" maxlength="3">
	<p><input name="submit" type="submit" value="{gt text="Edit Figure"}" /></p>
</form>