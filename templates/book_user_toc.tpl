{*  book_user_toc.tpl,v 1.5 2007/03/15 17:32:44 paustian Exp  *}
{ book_treemenu_include }
<h2>{$book.name}</h2>
<p>
{foreach item=chapter from=$chapters}
{$chapter}
{/foreach}
</p>