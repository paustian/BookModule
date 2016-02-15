{*  book_user_displayglossary.tpl,v 1.2 2006/01/02 01:51:12 paustian Exp  *}
<div class="book_glossary">
<h3>Glossary</h3>
{section name=i loop=$glossary}
<a name="{$glossary[i].term}"></a>
<p class="glossterm">
{$glossary[i].term}
</p>
<p class="glossdef">
{$glossary[i].definition}
</p>
{/section}
</div>