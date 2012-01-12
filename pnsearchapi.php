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

/**
 * Search plugin info
 **/
function book_searchapi_info() {
    return array('title' => 'Book',
            'functions' => array('Book' => 'search'));
}

/**
 * Search form component
 **/
function book_searchapi_options($args) {
    if (SecurityUtil::checkPermission( 'Book::', '::', ACCESS_READ)) {
        $pnRender = pnRender::getInstance('Book');
        return $pnRender->fetch('book_search_options.htm');
    }

    return '';
}


/**
 * Search plugin main function
 **/
function book_searchapi_search($args) {

    pnModDBInfoLoad('Search');
    $pntable = pnDBGetTables();
    $bookcolumn = $pntable['book_column'];

    $where = search_construct_where($args,
            array($bookcolumn['title'],
            $bookcolumn['contents']));

    $sessionId = session_id();

   pnModAPILoad('Book', 'user');

    $permChecker = new book_result_checker();
    $stories = DBUtil::selectObjectArrayFilter('book', $where, null, null, null, '', $permChecker, null);
    if(!$stories){
        //not found, return true
        return true;
    }
    $objArray = array();
    foreach ($stories as $story) {
        $obj = array();
        $obj['title'] = DataUtil::formatForStore($story['title']);
        $contents = shorten_text($story['contents']);
        $obj['text'] = DataUtil::formatForStore($contents);
        $obj['extra'] = DataUtil::formatForStore($story['art_id']);
        $obj['module'] =  'Book';
        $obj['created'] =   DataUtil::formatForStore(date("Y-m-d H:i:s"));
        $obj['session'] =   DataUtil::formatForStore($sessionId);
        $objArray[] = $obj;

    }
    $insertResult = DBUtil::insertObjectArray($objArray, 'search_result');
    if (!$insertResult) {
        return LogUtil::registerError (_GETFAILED);
    }

    return true;
}

/**
 * private function to shorten the contents text string
 * I think the search display stuff should be doing this
 * but it is not
 */
function shorten_text($text) {
// Change to the number of characters you want to display
    $chars = 500;
    $text = $text." ";
    $text = substr($text,0,$chars);
    $text = substr($text,0,strrpos($text,' '));
    $text = $text."...";

    return $text;
}

/**
 * Do last minute access checking and assign URL to items
 *
 * Access checking is ignored since access check has
 * already been done. But we do add a URL to the found item
 */
function book_searchapi_search_check(&$args) {

    //stopped here. have to make right URL. Also, does not work with search form
    $datarow = &$args['datarow'];
    $artId = $datarow['extra'];
    $datarow['url'] = pnModUrl('Book', 'user', 'displayarticle', array('art_id' => $artId));
    //var_dump(debug_backtrace()); die;
    return true;
}
