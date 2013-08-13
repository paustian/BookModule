<li><a href="{pnmodurl modname="Book" type="user" func="displayarticlesinchapter" cid=$chapter.cid}">{gt text="Chapter "} {$chapter.number} {$chapter.name}</a>
<ul>
{foreach item=article from=$articles}
<li>{$chapter.number}-{$article.aid} {$article.title}{if $show_internals}artid:{$article.aid}; next:{$article.next}; prev:{$article.prev}{/if}</li>
{/foreach}
</ul>
</li>