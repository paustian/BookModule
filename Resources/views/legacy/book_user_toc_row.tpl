
    <li><a href="{modurl modname="Book" type="user" func="displayarticlesinchapter" cid=$chapter.cid}">Chapter {$chapter.number} {$chapter.name} {if $show_internals}Chapter id:{$chapter.cid}{/if}</a></li>
    <ul><!-- aritcle-->

    {foreach item=article from=$articles}
        <li>
            {if $editmode eq 1}
                <input name="chosen_article" type="radio" value="{$article.aid}" />
            {/if}
            <a href="{modurl modname="Book" type="user" func="displayarticle" aid=$article.aid}"> {$chapter.number}-{$article.number} {$article.title} {if $show_internals}artid:{$article.aid}; next:{$article.next}; prev:{$article.prev}{/if}</a>
        </li>
    {/foreach}
    </ul><!-- aritcle close-->
