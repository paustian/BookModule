<?php

/**
 * PostNuke Application Framework
 *
 * @copyright (c) 2008, Timothy Paustian
 * @link http://www.microbiologytext.com
 * @version $Id: pnsearchapi.php 22139 2008-02-07 10:57:16Z Timothy Paustian $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package
 * @subpackage Book
 */

class Book_Api_Search extends Zikula_AbstractApi {

    /**
     * Search plugin info
     * */
    public function info() {
        return array('title' => 'Book',
            'functions' => array('Book' => 'search'));
    }

    /**
     * Search form component
     * */
    public function options($args) {
        if (SecurityUtil::checkPermission('Book::', '::', ACCESS_READ)) {
            $view = Zikula_View::getInstance('Book');
            return $view->fetch('book_search_options.htm');
        }

        return '';
    }

    /**
     * Search plugin main function
     * */
    public function search($args) {
        ModUtil::dbInfoLoad('Search');
        $pntable = DBUtil::getTables();
        $bookcolumn = $pntable['book_column'];

        $where = Search_Api_User::construct_where($args, array($bookcolumn['title'],
            $bookcolumn['contents']));

        $sessionId = session_id();

        ModUtil::loadApi('Book', 'user');

        $permChecker = new Book_ResultChecker();
        $stories = DBUtil::selectObjectArrayFilter('book', $where, null, null, null, '', $permChecker, null);
        if (!$stories) {
            //not found, return true
            return true;
        }
        $objArray = array();
        foreach ($stories as $story) {
            $obj = array();
            $obj['title'] = DataUtil::formatForStore($story['title']);
            $contents = $this->shorten_text($story['contents']);
            $obj['text'] = DataUtil::formatForStore($contents);
            $obj['extra'] = DataUtil::formatForStore($story['aid']);
            $obj['module'] = 'Book';
            $obj['created'] = DataUtil::formatForStore(date("Y-m-d H:i:s"));
            $obj['session'] = DataUtil::formatForStore($sessionId);
            $objArray[] = $obj;
        }
        $insertResult = DBUtil::insertObjectArray($objArray, 'search_result');
        if (!$insertResult) {
            return LogUtil::registerError(_GETFAILED);
        }

        return true;
    }

    /**
     * private function to shorten the contents text string
     * I think the search display stuff should be doing this
     * but it is not
     */
    private function shorten_text($text) {
// Change to the number of characters you want to display
        $chars = 500;
        $text = $text . " ";
        $text = substr($text, 0, $chars);
        $text = substr($text, 0, strrpos($text, ' '));
        $text = $text . "...";

        return $text;
    }

    /**
     * Do last minute access checking and assign url to items
     *
     * Access checking is ignored since access check has
     * already been done. But we do add a url to the found item
     */
    public function search_check($args) {

        //stopped here. have to make right url. Also, does not work with search form
        $datarow = &$args['datarow'];
        $artId = $datarow['extra'];
        $datarow['url'] = ModUtil::url('Book', 'user', 'displayarticle', array('aid' => $artId));
        //var_dump(debug_backtrace()); die;
        return true;
    }

}

class Book_ResultChecker
{
    // This method is called by DBUtil::selectObjectArrayFilter() for each and every search result.
    // A return value of true means "keep result" - false means "discard".
    function checkResult(&$item)
    {
        $ok = (SecurityUtil::checkPermission('Book::', "$item[bid]::$item[chapter_id]", ACCESS_OVERVIEW));
        return $ok;
    }
}
?>