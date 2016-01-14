{*  book_admin_modifychapter.tpl,v 1.3 2006/01/02 01:51:12 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="Edit Chapter"}</h2>
<form class="form" action="{modurl modname="Book" type="admin" func="updatechapter"}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />
    <table border>
     <tr>
     <td>
     	{gt text="Edit Chapter"}
    	</td>
    	<td>
    		{gt text="Chapter Name"}
    </td>
    	<td>
    		{gt text="Chapter Number"}
    	</td>
    	<td>
    		{gt text="Book that chapter belongs to"}
    	</td>
    	</tr>
    {section name=i loop=$chaps}
   <tr>
   <td>
   		<input type="radio" name="cid" value="{$chaps[i].cid}" {if $smarty.section.i.index == 0}checked>{else}>{/if}
   </td>
    <td>
    		<input type="text" size="40" max="256" name="title_{$chaps[i].cid}" value="{$chaps[i].name|}">
    	</td>
    <td>
    		<input type="text" size="5" max="5" name="number_{$chaps[i].cid}" value="{$chaps[i].number|}">
    	</td>
    <td>
  	<select name="bid_{$chaps[i].cid}">
  		 {$books[i]}
 	</select>
  	</td>
    </tr>
	{/section}
	</table>
	<p><input name="submit" type="submit" value="{gt text="Update Chapter"}" /></p>
</form>