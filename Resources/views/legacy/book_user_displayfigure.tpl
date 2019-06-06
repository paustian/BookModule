{*  book_user_displayfigure.tpl,v 1.4 2006/01/17 03:57:05 paustian Exp  *}
<div class="book_figure">
<div class="image">
{{ img_link }}
</div>

<p class="figure"><b>Figure {$chap_number}-{$fig_number}. {$title}</b>.
{if $content_empty == true}
{$content}
{/if}
</p>
</div>