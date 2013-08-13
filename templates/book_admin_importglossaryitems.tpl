{*  book_admin_importglossaryitems.tpl,v 1.3 2006/01/02 01:51:12 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="Import Glossary"}</h2>
<p>{gt text="To import glossary items, format them as an xml list using the following format"}</p>
<p>&lt;glossitem&gt;<br />
&lt;term&gt;<b>{gt text="term"}</b>&lt;/term&gt;<br />
&lt;definition&gt;<b>{gt text="definition"}</b> &lt;/definition&gt;<br />
&lt;/glossitem&gt;<br />
</p>
<form class="form" action="{pnmodurl modname="Book" type="admin" func="doglossaryimport"}" method="post" enctype="application/x-www-form-urlencoded">
<input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
<textarea name="gloss_text" cols="120" rows="30">
</textarea>
<p><input name="submit" type="submit" value="{gt text="Import Glossary"}" /></p>
</form>