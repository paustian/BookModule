{*  book_user_main.tpl,v 1.4 2006/12/23 22:59:01 paustian Exp  *}
<div class="book_main">
{section name=i loop=$names}
<p><a href="{$names[i].url}">{$names[i].title}</a></p>
{/section}
</div>