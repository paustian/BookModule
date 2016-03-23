{*  book_user_main.tpl,v 1.4 2006/12/23 22:59:01 paustian Exp  *}
{if $uid > 0}
{gt text="To purchase a one year subscription to this book, click the subscribe button below."}
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="{$hosted_button_id}">
<input type="hidden" name="custom" value="{$custom}">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_subscribeCC_LG.gif" border="0" name="submit" alt="">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
{else}
<p>{gt text="You need to register first before you can subscribe to the book. To do this go to the registration page"}</p>
{/if}