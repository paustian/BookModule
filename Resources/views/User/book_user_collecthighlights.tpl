{*  book_user_collecthighlights.tpl,v 1.2 2007/02/04 03:41:47 paustian Exp  *}
<h2>{gt text="Collect Highlights"}</h2>
{section loop=$content name=i}
<h3>{$chapter[i]} - {$section[i]}  {$title[i]}</h3> 
<p>
	{$content[i]}
</p>
<p><a href="{modurl modname="Book" type="user" func="displayarticle" aid=$aids[i]}">{gt text="link to content..."}</a>
<hr>
{/section}