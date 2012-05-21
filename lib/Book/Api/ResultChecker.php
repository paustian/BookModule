<?php

/**
 * Internal callback class used to check permissions to each News item
 * @author Timothy Paustian
 */
class Book_ResultChecker
{
    // This method is called by DBUtil::selectObjectArrayFilter() for each and every search result.
    // A return value of true means "keep result" - false means "discard".
    function checkResult(&$item)
    {
        $ok = (SecurityUtil::checkPermission('Book::', "$item[book_id]::$item[chapter_id]", ACCESS_OVERVIEW));
        return $ok;
    }
}
?>