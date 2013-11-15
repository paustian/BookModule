{*  book_user_displayarticle.tpl,v 1.8 2007/03/15 17:32:43 paustian Exp  *}
<div class="book_content">
<h1>{$number}-{$art_number} {$title|pnvarcensor}</h1>
{if $counter==1}
<p>({$counter} Read)</p>
{else}
<p>({$counter} Reads)</p>
{/if}
{themesetvar name="number" value=$number}
{if $show_internals}
<form class="form" action="{pnmodurl modname="Book" type="admin" func="modifyarticle2"}" method="post" enctype="application/x-www-form-urlencoded">
   <p><input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
    <input type="hidden" name="chosen_article" value="{$aid}" />
	<input name="submit" type="submit" value="{gt text="Edit article"}" /></p>
</form>
<form class="form" action="{pnmodurl modname="Book" type="admin" func="addglossaryitems"}" method="post" enctype="application/x-www-form-urlencoded">
   <p><input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
    <input type="hidden" name="aid" value="{$aid}" />
	<input name="submit" type="submit" value="{gt text="Add Glossary Items"}" /></p>
</form>
<p>article id: {$aid}</p>
<p>chapter id: {$cid}</p>
<p>book id: {$bid}</p>
{/if}
<p> <a href="{pnmodurl modname="Book" type="user" func="toc" bid=$bid}">{gt text="Table of Contents"}</a>|
<a href="{pnmodurl modname="Book" type="user" func="displayarticlesinchapter" cid=$cid}">{gt text="Chapter Article List"}</a>|
<a href="{pnmodurl modname="Book" type="user" func="displayarticle" aid=$aid theme=Printer}">{gt text="Printable Version"}</a>
| <a href="{pnmodurl modname="Book" type="user" func="displaychapter" cid=$cid theme=Printer}">{gt text="Printable Chapter"}</a></p>
{if $prev!=0}
<a href="{pnmodurl modname="Book" func="displayarticle" aid=$prev}">[{gt text="Prev"}]</a>
{/if}
{if $next!=0 && $prev!=0}
|
{/if}
{if $next!=0}
<a href="{modurl modname="Book" func="displayarticle" aid=$next}">[{gt text="Next"}]</a><br />
{/if}
{$content|pnvarcensor}

{if $prev!=0}
<a href="{pnmodurl modname="Book" func="displayarticle" aid=$prev}">[{gt text="Prev"}]</a>
{/if}
{if $next!=0 && $prev!=0}
|
{/if}
{if $next!=0}
<a href="{pnmodurl modname="Book" func="displayarticle" aid=$next}">[{gt text="Next"}]</a><br />
{/if}

<p> <a href="{pnmodurl modname="Book" type="user" func="toc" bid=$bid}">{gt text="Table of Contents"}</a>|
<a href="{pnmodurl modname="Book" type="user" func="displayarticlesinchapter" cid=$cid}">{gt text="Chapter Article List"}</a>|
<a href="{pnmodurl modname="Book" type="user" func="displayarticle" aid=$aid theme=Printer}">{gt text="Printable Version"}</a>
<a href="{pnmodurl modname="Book" type="user" func="displaychapter" cid=$cid theme=Printer}">{gt text="Printable Chapter"}</a></p>
{notifydisplayhooks eventname='book.ui_hooks.articles.display_view' id=$aid urlobject=$returnurl}
</div>