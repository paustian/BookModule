{*  book_user_view.tpl,v 1.3 2006/01/02 01:51:12 paustian Exp  *}
<h2>{$book|}</h2>
{section name=i loop=$titles}
<p>{gt text="Chapter"} {$number[i]|} - <a href="{modurl modname="Book" func="display" cid=$ids[i]}">{$titles[i]|}</a><br/>
{/section}