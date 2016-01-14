{*  book_user_display.tpl,v 1.4 2006/12/23 22:59:01 paustian Exp  *}
<div class="book_display">
<h2>{$chapter|}</h2>
<table>
{section name=i loop=$titles}
<tr><td>{$section_number[i]|}</td><td> <a href="{modurl modname="Book" func="displayarticle" aid=$ids[i]}">{$titles[i]|}</a></td></tr>
{/section}
</table>
</div>