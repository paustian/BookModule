<?php

// ----------------------------------------------------------------------
// PostNuke Content Management System
// Copyright (C) 2002 by the PostNuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on:
// PHP-NUKE Web Portal System - http://phpnuke.org/
// Thatware - http://thatware.org/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file: Timothy Paustian
// Purpose of file:  Book user API
// ----------------------------------------------------------------------

class Book_Api_User extends Zikula_AbstractApi {

    /**
     * get the complete list of books
     * @returns array
     * @return array of items, or false on failure
     */
    public function getall($args) {

        //check to see how many books there are
        $numitems = ModUtil::apiFunc('Book', 'user', 'countitems');
        if ($numitems == 0) {
            SessionUtil::setVar('errormsg', __('There are no books defined. Create a book first.'));
            return false;
        }
        // create a empty result set
        $items = array();
        $permFilter = array(array('realm' => 0,
                'component_left' => 'Book',
                'component_middle' => '',
                'component_right' => '',
                'instance_left' => 'bid',
                'instance_middle' => '',
                'instance_right' => '',
                'level' => ACCESS_OVERVIEW));

        $items = DBUtil::selectObjectArray('name', '', 'bid', 0, $numitems, '', $permFilter);

        if ($items === false) {
            return LogUtil::registerError(_GETFAILED);
        }
        // Return the items
        return $items;
    }

    /**
     * given a bid return the data for that book
     * @param $args['bid'] id of book item to get
     * @returns array
     */
    public function get($args) {
        $bid = $args['bid'];
        // Argument check - make sure that all required arguments are present, if
        // not then set an appropriate error message and return
        if (!isset($bid)) {
            LogUtil::registerError(__('bid not set in userapi_get'));
            return false;
        }
        // create a empty result set
        $item = array();
        $permFilter = array(array('realm' => 0,
                'component_left' => 'Book',
                'component_middle' => '',
                'component_right' => '',
                'instance_left' => 'bid',
                'instance_middle' => '',
                'instance_right' => '',
                'level' => ACCESS_OVERVIEW));
        I need the change this to Doctrine I need to figure out the permissions 
        and how to handle them wiht doctrine. May not be able to.
        $item = DBUtil::selectObjectByID('name', $bid, 'bid', null, $permFilter);

        if ($item === false) {
            return LogUtil::registerError(_GETFAILED);
        }
        // Return the items
        return $item;
    }

    /**
     * getallchapters
     *
     * given a bid retrieve all chapter
     * information that corresponds to it.
     *
     * @param bid the id of the book
     * @param all_chapters a switch if you want to get all chapters
     * @return array of arrays, the data for the chapter.
     */
    public function getallchapters($args) {
        $all_chapters = false;
        // Argument check - make sure that all required arguments are present, if
        // not then set an appropriate error message and return

        if (!isset($args['bid'])) {
            //you cannot say just one books chapters
            //and not specify a book id
            $all_chapters = true;
        }

        //anyone can look at chapters and book titles
        $permFilter = array(array('realm' => 0,
                'component_left' => 'Book',
                'component_middle' => '',
                'component_right' => 'Chapter',
                'instance_left' => 'bid',
                'instance_middle' => '',
                'instance_right' => 'cid',
                'level' => ACCESS_OVERVIEW));

        // Get datbase setup
        $pntable = & DBUtil::getTables();
        $bookChapList = &$pntable['book_chaps_column'];
        // Get item

        if ($all_chapters) {
            $items = DBUtil::selectObjectArray('book_chaps', '', 'number', -1, -1, '', $permFilter);
        } else {
            $where = "WHERE " . $bookChapList['bid'] . " = '" . DataUtil::formatForStore($args['bid']) . "'";
            $items = DBUtil::selectObjectArray('book_chaps', $where, 'number', -1, -1, '', $permFilter);
        }
        if ($items === false) {
            return LogUtil::registerError(__("Getting chapters failed"));
        }
        // Return the item array
        return $items;
    }

    /**
     * get a specific chapter
     * @param $args['cid'] id of example item to get
     * @returns array
     * @return item array, or false on failure
     */
    public function getchapter($args) {
        // Get arguments from argument array
        $cid = $args['cid'];

        // Argument check
        if (!isset($cid)) {
            LogUtil::registerError(_MODARGSERROR . "getchapter");
            return false;
        }
        // create a empty result set
        $item = array();
        $permFilter = array(array('realm' => 0,
                'component_left' => 'Book',
                'component_middle' => '',
                'component_right' => 'Chapter',
                'instance_left' => 'bid',
                'instance_middle' => '',
                'instance_right' => 'cid',
                'level' => ACCESS_OVERVIEW));

        $item = DBUtil::selectObjectByID('book_chaps', $cid, 'cid', null, $permFilter);

        if ($item === false) {
            return LogUtil::registerError(_GETFAILED);
        }
        // Return the items
        return $item;
    }

    /**
     * given a chapter id, return all articles that
     * are in that chapter.
     * The item array will be an array of arrays each containing
     * 	aid
     * 	title
     * 	cid
     * 	contents
     * 	count
     * 	next
     * 	prev
     * aid
     *
     * I may want to optimize this if returning all the text slows it down.
     * @param	cid the id of the chapter
     * @return item array, or false on failure
     *
     *
     */
    public function getallarticles($args) {
        // Get arguments from argument array
        $cid = $args['cid'];
        $get_content = $args['get_content'];

        if ((!isset($get_content))) {
            $get_content = true;
        }
        // Argument check
        if (!isset($cid)) {
            LogUtil::registerError(_MODARGSERROR . "getallarticles");
            return false;
        }


        // Get datbase setup
        $pntable = & DBUtil::getTables();
        $bookArtList = &$pntable['book_column'];
        $where = "WHERE $bookArtList[cid] = '" . DataUtil::formatForStore($cid) . "'";

        $items = array();
        if ($get_content) {
            $permFilter = array(array('realm' => 0,
                    'component_left' => 'Book',
                    'component_middle' => '',
                    'component_right' => 'Chapter',
                    'instance_left' => 'bid',
                    'instance_middle' => '',
                    'instance_right' => 'cid',
                    'level' => ACCESS_READ));
            $items = DBUtil::selectObjectArray('book', $where, 'aid', -1, -1, '', $permFilter);
        } else {
            //anyone can look at chapters and book titles
            $permFilter = array(array('realm' => 0,
                    'component_left' => 'Book',
                    'component_middle' => '',
                    'component_right' => 'Chapter',
                    'instance_left' => 'bid',
                    'instance_middle' => '',
                    'instance_right' => 'cid',
                    'level' => ACCESS_OVERVIEW));
            $columns = array('title', 'aid', 'bid', 'counter', 'lang', 'next', 'prev', 'aid');
            $items = DBUtil::selectObjectArray('book', $where, 'aid', -1, -1, '', $permFilter, null, $columns);
        }

        if ($items === false) {
            return LogUtil::registerError(_GETFAILED);
        }
        // Return the items
        return $items;
    }

    /**
     * get a specific article
     * @param $args['aid'] id of example item to get
     * @returns array
     * @return item array, or false on failure
     */
    public function getarticle($args) {
        $aid = $args['aid'];
        // Argument check - make sure that all required arguments are present, if
        // not then set an appropriate error message and return
        if (!isset($aid)) {
            LogUtil::registerError(_MODARGSERROR . "get");
            return false;
        }
        // create a empty result set
        $item = array();
        $permFilter = array(array('realm' => 0,
                'component_left' => 'Book',
                'component_middle' => '',
                'component_right' => 'Chapter',
                'instance_left' => 'bid',
                'instance_middle' => '',
                'instance_right' => 'cid',
                'level' => ACCESS_READ));

        $item = DBUtil::selectObjectByID('book', $aid, 'aid', null, $permFilter);

        if ($item === false) {
            return LogUtil::registerError(_GETFAILED);
        }
        // Return the items
        return $item;
    }

    /**
     * get a the next article as specifed by the article number and chapter
     * @param $args['aid'] the article number of the next article
     * @param $args['cid'] the chapter id of the article.
     * @returns array
     * @return item array, or false on failure
     */
    public function getarticlebyartnumber($args) {
        // Argument check - make sure that all required arguments are present, if
        // not then set an appropriate error message and return
        if (!isset($args['aid']) || !isset($args['cid'])) {
            LogUtil::registerError(_MODARGSERROR . "get");
            return false;
        }

        $permFilter = array(array('realm' => 0,
                'component_left' => 'Book',
                'component_middle' => '',
                'component_right' => 'Chapter',
                'instance_left' => 'bid',
                'instance_middle' => '',
                'instance_right' => 'cid',
                'level' => ACCESS_READ));
        //set up the where clause
        $pntable = & DBUtil::getTables();
        $artFigList = &$pntable['book_column'];
        $where = "WHERE $artFigList[aid]= '" . DataUtil::formatForStore($args['aid']) . "'" .
                " AND $artFigList[cid] = '" . DataUtil::formatForStore($args['cid']) . "'";

        $item = DBUtil::selectObject('book', $where, '', $permFilter);

        if ($item === false) {
            return LogUtil::registerError(_GETFAILED);
        }
        // Return the items
        return $item;
    }

    public function getallfigures($args) {
        if ((!isset($args['bid']))) {
            $bid = -1;
        } else {
            $bid = $args['bid'];
        }
        $items = array();
        //set up the where clause
        $pntable = & DBUtil::getTables();
        $bookFigList = &$pntable['book_figures_column'];
        $where = "WHERE $bookFigList[bid]= '" . DataUtil::formatForStore($bid) . "'";

        //set up the premission filter
        $permFilter = array(array('realm' => 0,
                'component_left' => 'Book',
                'component_middle' => '',
                'component_right' => '',
                'instance_left' => 'bid',
                'instance_middle' => '',
                'instance_right' => '',
                'level' => ACCESS_OVERVIEW));
        // Get item

        if ($bid == -1) {
            $items = DBUtil::selectObjectArray('book_figures', '', 'number', -1, -1, '', $permFilter);
        } else {
            $items = DBUtil::selectObjectArray('book_figures', $where, 'number', -1, -1, '', $permFilter);
        }
        //check for errors
        if ($items === false) {
            return LogUtil::registerError(_GETFAILED);
        }

        // Return the item array
        return $items;
    }

    /**
     * getallglossary
     *
     * return all glossary items
     *
     * @param get_definitions Also return all the defnitions as well as the terms
     * @return an array of all glossary items (gid, terms, definitions (optional))
     */
    public function getallglossary($args) {
        // Argument check - make sure that all required arguments are present, if
        // not then set an appropriate error message and return
        if (!isset($args['get_defs'])) {
            $get_defs = true;
        } else {
            // Get arguments from argument array
            $get_defs = $args['get_defs'];
        }

        //set upu the return array
        $items = array();
        //Check general permissions, they must have read access for the glossary
        // Security check -
        if (!SecurityUtil::checkPermission('Book::Chapter', ".*::.*", ACCESS_READ)) {
            return DataUtil::formatForDisplayHTML(__('You do not have permission to view that book.'));
        }

        if ($get_defs) {
            $items = DBUtil::selectObjectArray('book_glossary', '', 'term');
        } else {
            $columns = array('gid', 'term');
            $items = DBUtil::selectObjectArray('book_glossary', '', 'term', -1, -1, '', null, null, $columns);
        }

        //check for errors
        if ($items === false) {
            return LogUtil::registerError(_GETFAILED);
        }

        // Return the item array
        return $items;
    }

    /**
     * findglossaryterm
     *
     * find out if a glossary term is in the glossary.
     */
    public function findglossaryterm($args) {
        $term = $args['term'];

        if (!isset($term)) {
            return true;
        }
        $pntable = & DBUtil::getTables();
        $glossaryList = &$pntable['book_glossary_column'];
        $where = "WHERE $glossaryList[term] LIKE '%" . DataUtil::formatForStore($term) . "%'";

        $items = array();
        $columns = array('term');
        $items = DBUtil::selectObjectArray('book_glossary', $where, '', -1, -1, '', null, null, $columns);
        //check for errors
        if ($items === false) {
            return LogUtil::registerError(_GETFAILED);
        }
        //this is not an error, the value was not found, which is an OK result
        if (empty($items)) {
            return false;
        }

        return true;
    }

    /**
     * getglossary
     *
     * Given a specific gid, return the term and definition
     *
     * @param gid the id of the glossary item
     * @return an array of the glossary data for that item
     */
    public function getglossary($args) {
        // Get arguments from argument array
        $gid = $args['gid'];
        $user = $args['user'];

        // Argument check - make sure that all required arguments are present, if
        // not then set an appropriate error message and return
        if (!isset($gid) && !isset($user)) {
            LogUtil::registerError(_MODARGSERROR . "getglossary");
            return false;
        }
        //general permission check. You have to be able to access the book, but we don't need a permission filter
        if (!SecurityUtil::checkPermission('Book::Chapter', ".*::.*", ACCESS_READ)) {
            return DataUtil::formatForDisplayHTML(__("You do not have permission to view that book."));
        }
        $item = array();
        // Get item
        if (isset($gid)) {
            $item = DBUtil::selectObjectByID('book_glossary', $gid, 'gid');
        } else {
            $item = DBUtil::selectObjectByID('book_glossary', $user, 'user');
        }
        //check for errors
        if ($item === false) {
            return LogUtil::registerError(_GETFAILED);
        }
        // Return the item array
        return $item;
    }

    /**
     * get a specific figure
     * @param 'fid' id of figure to get
     * @returns array
     * @return item array, or false on failure
     */
    public function getfigure($args) {
        //you can find a figure by id or by book, chapter and figure number
        if (!isset($args['fid'])) {
            if (!isset($args['fig_number']) || !isset($args['chap_number']) || !isset($args['bid'])) {
                LogUtil::registerError(__('Variable error getfigure'));
                return false;
            }
            $fig_number = $args['fig_number'];
            $chap_number = $args['chap_number'];
            $bid = $args['bid'];
        } else {
            $fid = $args['fid'];
        }

        // Get datbase setup
        $pntable = & DBUtil::getTables();
        $bookFigList = &$pntable['book_figures_column'];
        $item = array();
        // Get all the information on the item.
        if (isset($fid)) {
            $item = DBUtil::selectObjectByID('book_figures', $fid, 'fid');
        } else {
            $items = array();
            $where = "WHERE $bookFigList[fig_number]='" . DataUtil::formatForStore($fig_number) . "'" .
                    " AND $bookFigList[chap_number]='" . DataUtil::formatForStore($chap_number) . "'" .
                    " AND  $bookFigList[bid]='" . DataUtil::formatForStore($bid) . "'";
            //This should pick out a unqiue item
            $item = DBUtil::selectObject('book_figures', $where);
        }
        if ($item === false) {
            return LogUtil::registerError(_GETFAILED);
        }
        // We should never get here
        return $item;
    }

    /**
      /**
     * utility function to count the number of items held by this module
     * @returns integer
     * @return number of items held by this module
     */
    public function countitems() {
        $count = DBUtil::selectObjectCount('name');
        return $count;
    }

    /**
     * count_chapters
     * utility function to count the number of chapters in a book
     * @param $args['bid'] the ID of the book in question
     * @rerturns the number of chapters in the book
     */
    public function countchapters($args) {

        $bid = $args['bid'];

        if (!(isset($bid))) {
            return false;
        }
        $pntable = & DBUtil::getTables();
        $chapList = $pntable['book_chaps_column'];
        $where = "WHERE " . $chapList['bid'] . "=" . DataUtil::formatForStore($bid);
        $count = DBUtil::selectObjectCount('book_chaps', $where);
        return $count;
    }

    /**
     * setcoutner
     * Set the counter on a specific article ID. Note that you have to pass in
     * the number that you want for the counter. In this way it is possible to
     * reset the counter if you want at some point.
     * @param	$aid the id of the article to set the count of
     * @param	$counter the value to set the counter to
     *
     * @returns true or false depending upon whether the setting was successful.
     *
     */
    public function setcounter($args) {
        $aid = $args['aid'];
        $counter = $args['counter'];

        if (!isset($aid) || !isset($counter)) {
            LogUtil::registerError(_MODARGSERROR . "incrementcounter");
            return false;
        }

        $res = DBUtil::incrementObjectFieldByID('book', 'counter', $aid, 'aid');

        if ($res === false) {
            return LogUtil::registerError(_GETFAILED);
        }
        return $res;
    }

    /**
     * gethighlights
     *
     * Given a uid and an aid, find the highlights delimeters
     * and return them
     *
     */
    public function gethighlights($args) {
        //You have to have read access to get this
        if (!SecurityUtil::checkPermission('Book::Chapter', ".*::.*", ACCESS_READ)) {
            return null;
        }
        // Get arguments from argument array
        $uid = $args['uid'];
        $aid = $args['aid'];

        if (!isset($uid)) {
            LogUtil::registerError(_MODARGSERROR . "gethighlights");
            return false;
        }

        // Get datbase setup
        $pntable = & DBUtil::getTables();
        $bookDataList = &$pntable['book_user_data_column'];
        $order_by = "ORDER BY $bookDataList[start] ASC";

        if (isset($aid)) {
            $where = "WHERE $bookDataList[uid] = '" . DataUtil::formatForStore($uid) . "'
				AND  $bookDataList[aid] = '" . DataUtil::formatForStore($aid) . "'";
        } else {
            $where = "WHERE $bookDataList[uid] = '" . DataUtil::formatForStore($uid) . "'";
        }
        $items = DBUtil::selectObjectArray('book_user_data', $where, $order_by);

        if ($items === false) {
            return LogUtil::registerError(_GETFAILED);
        }

        // Return the item array
        return $items;
    }

}

?>