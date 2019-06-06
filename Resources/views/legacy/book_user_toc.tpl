{*  book_user_toc.tpl,v 1.5 2007/03/15 17:32:44 paustian Exp  *}    
<div class="zikula-ui-box">
    <h2  id="link_panels">{$book.name}</h2>

    <div id="accordion">
        <ul>
        {foreach item=chapter from=$chapters}
            <li>{$chapter}</li>
        {/foreach}
        </ul>
    </div>
</div>