{*  book_admin_modifyarticle2.tpl,v 1.4 2006/01/02 01:51:12 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="Edit Article"}</h2>
<form class="form" action="{pnmodurl modname="Book" type="admin" func="updatearticle"}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
    <input type="hidden" name="aid" value="{$aid}" />
    <input type="hidden" name="bid" value="{$bid}" />
<p>Book: {$book|pnvarcensor}</p>
<p>Chapter: 
	<select name="chapter_id">
			{html_options options=$chap_menu selected=$selected_chapter}
	</select>
<p>
  {gt text="Title"} <input type="text" name="title" size="50" maxlength="256" value="{$title}"/>
</p>

<p>{gt text="Content"}</p>
<p><textarea id="book_article_contents" name="contents" cols="100" rows="18">{$contents}</textarea></p>
{pnconfiggetvar name='multilingual' assign='multilingual'}
{if $multilingual}
<div class="z-formrow">
    <label for="book_language">{gt text='Language'}</label>
    {languagelist id='book_language' name='lang' all=true installed=true selected=$language}
</div>
{/if}
<table>
<tr>
	<td>{gt text="ID"}</td><td>{$aid}</td>
</tr>
<tr>
	<td>{gt text="Previous"}</td><td><input type="text" name="prev" size="4" max="5"value="{$prev}"></td>
</tr>
<tr>
	<td>{gt text="Next"}</td><td><input type="text" name="next" value="{$next}" size="4" max="5"></td>
</tr>
<tr>
	<td>{gt text="Number"}</td><td><input type="text" name="number" size="4" max="5" value="{$number}"></td>
</tr>
</table>
    
	<p><input name="submit" type="submit" value="{gt text="Edit article"}" /></p>
</form>
{notifydisplayhooks eventname='book.ui_hooks.articles.form_edit' id=$aid}