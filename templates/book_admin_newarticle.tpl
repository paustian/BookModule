{*  book_admin_newarticle.tpl,v 1.3 2006/01/02 01:51:12 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<hr>
<h2>{gt text="Create a new article"}</h2>
<form class="form" action="{modurl modname="book" type="admin" func="createarticle"}" method="post" enctype="application/x-www-form-urlencoded">
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
<p>
  {gt text="Title"} <input type="text" name="title" size="50" maxlength="256"/>
</p>

<p>{gt text="Contents"}</p>
<p><textarea name="contents" id="contents" cols="100" rows="18" wrap="virtual"></textarea></p>
{if $modvars.ZConfig.multilingual}
<div class="z-formrow">
    <label for="book_language">{gt text='Language'}</label>
    {html_select_locales id='lang' name='lang' selected=$modvars.ZConfig.language_i18n installed=1 all=false class='form-control'}
</div>
{/if}
<table>
    <tr>
	<td>{gt text="Next"}</td><td><input type="text" name="next" size="4" max="5"></td>
</tr>
<tr>
	<td>{gt text="Previous"}</td><td><input type="text" name="prev" size="4" max="5"></td>
</tr>
<tr>
	<td>{gt text="Article Number"}</td><td><input type="text" name="number" value="1" size="4" max="5"></td>
</tr>
</table>
<p><input name="submit" type="submit" value="{gt text="Add Article"}" /></p>
</form>
{notifydisplayhooks eventname='book.ui_hooks.articles.form_edit' id=$aid}
  
