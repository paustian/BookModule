<?php

declare(strict_types=1);

namespace Paustian\BookModule\Helper;

class TagHelper
{
    /*
     * stringFrontAndBackTags - This function was necessary to remove initial <p> and ending paragraph tags
     * from content going into certain classes (Figure and Glossary) because of the Scribite editors adding them
     * @param inText - the Text to strip the <p> tag from
     * @return string
     *
     */
    public static function stripFrontAndBackPTags(string $inText) : string
    {
        //remove the initial paragraph tag
        $content = preg_replace('|^<p[^>]*>|im', '', $inText, 1);
        //remove the ending </p>
        $content = preg_replace('|[\s\S]*\K(</p>$)|m', '', $content, 1);
        return $content;
    }
}