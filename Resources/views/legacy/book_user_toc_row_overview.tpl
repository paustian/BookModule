<h3 class="acc_header"><a href="{modurl modname="Book" type="user" func="displayarticlesinchapter" cid=$chapter.cid}">Chapter {$chapter.number} {$chapter.name} {if $show_internals}Chapter id:{$chapter.cid}{/if}</a></h3>
<div>
    {foreach item=article from=$articles}
        <p>
           {$chapter.number}-{$article.number} {$article.title}
        </p>
    {/foreach}
</div>