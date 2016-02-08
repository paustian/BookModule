{*  book_admin_export.tpl,v 1.4 2007/01/05 04:19:50 paustian Exp  *}

<p>{gt text="Copy and paste this into whatever program you want and make your changes. When you are finished. Go to the import page and copy and paste it there."}</p>
<textarea cols="120" rows="30">
&lt;!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"&gt;
&lt;html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{lang}" lang="{lang}"&gt;
    &lt;head&gt;
    &lt;meta http-equiv="Content-type" content="text/html;charset=UTF-8" /&gt;
    &lt;title&gt;{gt text="Export Text"}&lt;/title&gt;
&lt;link rel="stylesheet" href="{$stylepath}/normal.css" type="text/css" media="screen,projection" /&gt;
&lt;/head&gt;
&lt;body&gt;
    <!--(bookname){$name}(/bookname)-->
{$export_text}
&lt;/body&gt;
&lt;/html&gt;
</textarea>
