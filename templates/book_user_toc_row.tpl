
<h3 class="acc_header" id="{$chapter.number}"><a href="{pnmodurl modname="Book" type="user" func="displayarticlesinchapter" cid=$chapter.cid}">Chapter {$chapter.number} {$chapter.name} {if $show_internals}Chapter id:{$chapter.cid}{/if}</a></h3>
<div>
    {foreach item=article from=$articles}
        <p>
            {if $editmode eq 1}
                <input name="chosen_article" type="radio" value="{$article.aid}" />
            {/if}
            <a href="{pnmodurl modname="Book" type="user" func="displayarticle" aid=$article.aid}"> {$chapter.number}-{$article.number} {$article.title} {if $show_internals}artid:{$article.aid}; next:{$article.next}; prev:{$article.prev}{/if}</a>
        </p>
    {/foreach}
</div>
