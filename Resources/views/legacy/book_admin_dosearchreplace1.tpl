{*  book_admin_dochapterdelete.tpl,v 1.6 2006/01/02 01:51:12 paustian Exp  *}
{include file="book_admin_menu.tpl"}
<h2>{gt text="Search and Replace in a Chapter/Book"}</h2>
<h2>{gt text="This is a powerful, but dangerous function. Construct your expresions carefully!"}</h2>
<form class="form" action="{modurl modname="Book" type="admin" func="dosearchreplace2"}" method="post" enctype="application/x-www-form-urlencoded">
      <div>
        <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Book"}" />

               <table>
            <tr><td>{gt text="Search Pattern"}:</td><td><input type="text" name="search_pat" size="50" value="{$search_pat}" /></td></tr>
            <tr><td>{gt text="Replace Pattern"}:</td><td><input type="text" name="replace_pat" size="50" value="{$replace_pat}" /></td></tr>
            <tr><td><input type="checkbox" name="preview" checked />{gt text="Show preview of search"}</td><td></td></tr>
            {section name=i loop=$books}
            <tr><td>
                    <input type="radio" name="bid" value="{$books[i].bid}" {if $smarty.section.i.index == 0}checked>{else}>{/if}{$books[i].name}
                </td>
                <td>
                    <select name="chapter_{$books[i].bid}">
                        <option label="{gt text="Search/Replace Entire Book"}" value="0">{gt text="_BOOKSEARCHREPLACEALL"}</option>
                        {html_options options=$chapters[i] selected=`$cid`}
                    </select>
                </td></tr>
            {/section}
        </table>
        <p><input name="submit" type="submit" value="{gt text="Do Search and Replace"}" /></p>
        <hr />
        {if $preview_text != ""}
        <h3>{gt text="Search and Replace Preview"}</h3>
        <p>{$preview_text}</p>
        {else}
        <h3>{gt text="No matches found."}</h3>
        {/if}
</form>