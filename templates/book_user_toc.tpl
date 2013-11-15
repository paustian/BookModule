{*  book_user_toc.tpl,v 1.5 2007/03/15 17:32:44 paustian Exp  *}
{pageaddvar name='javascript' value='jquery-ui'}
{pageaddvar name='javascript' value='modules/Book/javascript/jquery.cookie.js'}
{pageaddvar name='javascript' value='modules/Book/javascript/book.js'}
{pageaddvar name='stylesheet' value='javascript/jquery-ui/themes/base/jquery-ui.css'}
<script type="text/javascript">
    var $j = jQuery.noConflict();
    var myPanels;
    $j(function() {
        myPanels = $j("#accordion").accordion({
           change: function(event,ui) {
		var hid = ui.newHeader.context.id;
                if (hid === undefined) {
                        $j.cookie('menustate', null);
                } else {
                        $j.cookie('menustate', hid, {path: '/', expires: 31 });
                }
            }
        });
        var menuState = Number($j.cookie('menustate')) - 1;
        if(menuState) {
            myPanels.accordion( "activate", menuState );
        }
    });
</script>
<div class="zikula-ui-box">
    <h2  id="link_panels">{$book.name}</h2>

    <div id="accordion">
        {foreach item=chapter from=$chapters}
            {$chapter}
        {/foreach}
    </div>
</div>