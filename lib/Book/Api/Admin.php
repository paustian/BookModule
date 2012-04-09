<?php

// pnadminapi.php,v 1.10 2007/02/03 13:02:44 paustian Exp
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
// Purpose of file:  Book administration API
// ----------------------------------------------------------------------
class Book_Api_Admin extends Zikula_AbstractApi {

    public function create($args) {
// Argument check
        if ((!isset($args['book_name']))) {
            LogUtil::registerArgsError();
            return false;
        }

// Security check - important to do this as early on as possible to
// avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_ADD)) {
            LogUtil::registerPermissionError();
            return false;
        }
//insert a new object. The book_id is inserted into the $args array
        if (!DBUtil::insertObject($args, 'book_name', 'book_id')) {
            return LogUtil::registerError(__('Book creation failed.'));
        }

// Let any hooks know that we have created a new item.  As this is a
// create hook we're passing 'tid' as the extra info, which is the
// argument that all of the other functions use to reference this
// item
        ModUtil::callHooks('item', 'create', $args['book_id'], 'book_id');

// Return the id of the newly created item to the calling process
        return $book_id;
    }

    /**
     * create a new book item
     * @param $args['name'] name of the item
     * @returns int
     * @return book item ID on success, false on failure
     */
    public function createchapter($args) {
// Argument check
//we make sure that the number is 1 or greater. No zero or
//negative chapters
        if ((!isset($args['chap_name'])) ||
                (!isset($args['book_id']))) {
            LogUtil::registerArgsError();
            return false;
        }


        if (!isset($arts['chap_number']) || ($args['chap_number'] < 1)) {
//we need to generate a chapter number. Count the number of
//chapters and then add a 1 to it. This may fail if a
//chapter is missing, but its not fatal to have two chapters
//with the same number
            $args['number'] = ModUtil::apiFunc('Book', 'user', 'countchapters', array('book_id' => $book)) + 1;
        }
// Security check - important to do this as early on as possible to
// avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_ADD)) {
            LogUtil::registerPermissionError();
            return false;
        }

//insert a new object. The book_id is inserted into the $args array
        if (!DBUtil::insertObject($args, 'book_chaps', 'chap_id')) {
            return LogUtil::registerError(_CREATEFAILED);
        }
// Let any hooks know that we have created a new item.
        ModUtil::callHooks('item', 'create', $args['chap_id'], 'chap_id');

// Return the id of the newly created item to the calling process
        return $args['chap_id'];
    }

    /**
     * create a new book item
     * @param $args['title'] title of the article
     * @param $args['content'] content of the article
     * @param $args['next'] the next article to link to
     * @param $arg['prev'] the previous article to link to
     * @param $args['chapter'] the chatper this article belongs to
     * @param $args['lang'] the language of the article
     * @returns int
     * @return book item ID on success, false on failure
     */
    public function createarticle($args) {
// Argument check
//we make sure that all the arguments are there
//and that the chapter is larger than 1
//negative chapters
        if ((!isset($args['title'])) ||
                (!isset($args['book_id'])) ||
                (!isset($args['contents'])) ||
                (!isset($args['next'])) ||
                (!isset($args['prev'])) ||
                (!isset($args['art_number'])) ||
                (!isset($args['chap_id'])) || ($args['chap_id'] < 1) ||
                (!isset($args['lang']))) {
            LogUtil::registerArgsError();
            return false;
        }

        if ($args['next'] == '') {
            $args['next'] = 0;
        }
        if ($args['prev'] == '') {
            $args['prev'] = 0;
        }
        //add glossary terms
        $contents = $this->add_glossary_terms(array('in_text' => $args['contents']));
        $args['contents'] = $contents;

// Security check - important to do this as early on as possible to
// avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::Chapter', "::", ACCESS_ADD)) {
            LogUtil::registerPermissionError();
            return false;
        }
        //This is not working. for some reason the content is empty on insert. It has
        //    something to do with preserve.
        $preserve = false;
//    if(isset($args['art_id'])){
//        $preserve = true;
//    }
//insert a new object. The book_id is inserted into the $args array
        if (!DBUtil::insertObject($args, 'book', 'art_id', $preserve)) {
            return LogUtil::registerError(_CREATEFAILED . "article insert");
        }

// Let any hooks know that we have created a new item.
        ModUtil::callHooks('item', 'create', $args['art_id'], 'art_id');

// Return the id of the newly created item to the calling process
        return $args['art_id'];
    }

    public function createfigure($args) {
// Argument check
//we make sure that all the arguments are there
//and that the chapter is larger than 1
//negative chapters
        if ((!isset($args['fig_number'])) ||
                (!isset($args['chap_number'])) ||
                (!isset($args['fig_content'])) ||
                (!isset($args['fig_title'])) ||
                (!isset($args['fig_perm'])) ||
                (!isset($args['book_id'])) ||
                (!isset($args['img_link']))) {
            LogUtil::registerArgsError();
            return false;
        }
// Security check - important to do this as early on as possible to
// avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::Chapter', "::", ACCESS_ADD)) {
            LogUtil::registerPermissionError();
            return false;
        }

//insert a new object. The book_id is inserted into the $args array
        if (!DBUtil::insertObject($args, 'book_figures', 'fig_id')) {
            return LogUtil::registerError(_CREATEFAILED . "article insert");
        }
// Let any hooks know that we have created a new item.
        ModUtil::callHooks('item', 'create', $args['fig_id'], 'fig_id');

// Return the id of the newly created item to the calling process
        return $args['fig_id'];
    }

    public function createglossary($args) {
// Argument check
        if ((!isset($args['term'])) ||
                (!isset($args['definition']))) {
            LogUtil::registerArgsError();
            return false;
        }
        if ((!isset($args['user']))) {
            $args['user'] = "";
        }
        if ((!isset($args['URL']))) {
            $args['URL'] = "";
        }


// Security check
        if (!SecurityUtil::checkPermission('Book::Chapter', "::", ACCESS_ADD)) {
            LogUtil::registerPermissionError();
            return false;
        }

//insert a new object. The book_id is inserted into the $args array
        if (!DBUtil::insertObject($args, 'book_glossary', 'gloss_id')) {
            return LogUtil::registerError(__('Creating the glossary item failed, createglossary'));
        }
// Let any hooks know that we have created a new item.  As this is a
// create hook we're passing 'tid' as the extra info, which is the
// argument that all of the other functions use to reference this
// item
        ModUtil::callHooks('item', 'create', $gloss_id, 'gloss_id');

// Return the id of the newly created item to the calling process
        return $gloss_id;
    }

    /**
     * delete a book item
     * @param $args['tid'] ID of the item
     * @returns bool
     * @return true on success, false on failure
     */
    public function delete($args) {
// Argument check
        if (!isset($args['book_id'])) {
            LogUtil::registerArgsError();
            return false;
        }

// The user API function is called.
        $item = ModUtil::apiFunc('Book', 'user', 'get', array('book_id' => $args['book_id']));


        if ($item == false) {
            LogUtil::registerPermissionError();
            return false;
        }


// Security check
        if (!SecurityUtil::checkPermission('Book::', "$args[book_id]::", ACCESS_DELETE)) {
            LogUtil::registerPermissionError();
            return false;
        }

        if (!DBUtil::deleteObjectByID('book_name', $args['book_id'], 'book_id')) {
            return LogUtil::registerError(__('Deleting the book failed'));
        }

//Now delete all the articles and chapters
        $pntable = & DBUtil::getTables();
//delete all the articles associated with this book
        $articleList = &$pntable['book_column'];
        $where = "WHERE $articleList[book_id] = '" . DataUtil::formatForStore($args['book_id']) . "'";

        if (!DBUtil::deleteWhere('book', $where)) {
            return LogUtil::registerError(__('Deleting the articles of the book failed'));
        }
//delete all the chapters associated with this book
        $chapList = &$pntable['book_chaps_column'];
        $where = "WHERE $chapList[book_id] = '" . DataUtil::formatForStore($args['book_id']) . "'";

        if (!DBUtil::deleteWhere('book_chaps', $where)) {
            return LogUtil::registerError(__('Deleting the chapters of the book failed'));
        }

// Let any hooks know that we have deleted an item.  As this is a
// delete hook we're not passing any extra info
        ModUtil::callHooks('item', 'delete', $args['book_id'], '');

// Let the calling process know that we have finished successfully
        return true;
    }

    public function deletechapter($args) {
// Argument check - make sure that all required arguments are present,
// if not then set an appropriate error message and return
        if (!isset($args['chap_id'])) {
            LogUtil::registerArgsError();
            return false;
        }
// Security check
        if (!SecurityUtil::checkPermission('::Chapter', "::$args[chap_id]", ACCESS_DELETE)) {
            LogUtil::registerPermissionError();
            return false;
        }
        $pntable = & DBUtil::getTables();
//delete all the articles associated with this chapter
        $articleList = &$pntable['book_column'];
        $where = "WHERE $articleList[chap_id] = '" . DataUtil::formatForStore($args['chap_id']) . "'";

        if (!DBUtil::deleteWhere('book', $where)) {
            return LogUtil::registerError(__('Deleting the artilces in the chapter failed'));
        }

//finally delete the chapter
        if (!DBUtil::deleteObjectByID('book_chaps', $args['chap_id'], 'chap_id')) {
            return LogUtil::registerError(__('Deleting the chapter failed.'));
        }

// Let any hooks know that we have deleted an item.  As this is a
// delete hook we're not passing any extra info
        ModUtil::callHooks('item', 'deletechapter', $chap_id, '');

// Let the calling process know that we have finished successfully
        return true;
    }

    public function deletearticle($args) {
// Argument check
        if (!isset($args['art_id'])) {
            LogUtil::registerArgsError();
            return false;
        }
        $article = ModUtil::apiFunc('Book', 'user', 'getarticle', array('art_id' => $args['art_id']));

// Security check
        if (!SecurityUtil::checkPermission('Book::Chapter', "$article[book_id]::$article[chap_id]", ACCESS_DELETE)) {
            LogUtil::registerPermissionError();
            return false;
        }

        if (!DBUtil::deleteObjectByID('book', $args['art_id'], 'art_id')) {
            return LogUtil::registerError(__('Deleting the article failed.'));
        }
        ModUtil::callHooks('item', 'deletearticle', $args['art_id'], '');

// Let the calling process know that we have finished successfully
        return true;
    }

    public function deletefigure($args) {
// Argument check
        if (!isset($args['fig_id'])) {
            LogUtil::registerArgsError();
            return false;
        }
//security check
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_DELETE)) {
            LogUtil::registerPermissionError();
            return false;
        }
        if (!DBUtil::deleteObjectByID('book_figures', $args['fig_id'], 'fig_id')) {
            return LogUtil::registerError(__('Deleting the figure failed.'));
        }

        ModUtil::callHooks('item', 'deletefigure', $args['fig_id'], '');

// Let the calling process know that we have finished successfully
        return true;
    }

    public function deleteglossary($args) {
// Argument check
        if (!isset($args['gloss_id'])) {
            LogUtil::registerArgsError();
            return false;
        }
//security check
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_DELETE)) {
            LogUtil::registerPermissionError();
            return false;
        }
        if (!DBUtil::deleteObjectByID('book_glossary', $args['gloss_id'], 'gloss_id')) {
            return LogUtil::registerError(__('Deleting the glossary item failed.'));
        }
        ModUtil::callHooks('item', 'deleteglossary', $args['gloss_id'], '');

// Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * update a book item
     * @param $args['book_id'] the ID of the item
     * @param $args['name'] the new name of the item
     * @param $args['number'] the new number of the item
     */
    public function update($args) {
// Argument check
        if ((!isset($args['book_id'])) ||
                (!isset($args['book_name']))) {
            LogUtil::registerArgsError();
            return false;
        }
        $book_id = $args['book_id'];
// The user API function is called.  This takes the item ID which
// we obtained from the input and gets us the information on the
// appropriate item.  If the item does not exist we post an appropriate
// message and return
        $item = ModUtil::apiFunc('Book', 'user', 'get', array('book_id' => $book_id));

        if ($item == false) {
            //we don't throw an error, we depend on the caling funciton to do that.
            return false;
        }

// Security check
        if (!SecurityUtil::checkPermission('Book::Chapter', "$item[book_id]::.*", ACCESS_EDIT)) {
            LogUtil::registerPermissionError();
            return false;
        }

        if (!DBUtil::updateObject($args, 'book_name', '', 'book_id')) {
            return LogUtil::registerError(__('Updating the book failed.'));
        }

// Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * update a chapter item
     * @param $args['book_id'] the id of the book
     * @param $args['chap_name'] the title of the chapter
     * @param $args['chap_number'] the chapter number
     * @param $args['chap_id'] the unique ID of the chapter
     */
    public function updatechapter($args) {
// Argument check
        if ((!isset($args['book_id'])) ||
                (!isset($args['chap_name'])) ||
                (!isset($args['chap_number'])) ||
                (!isset($args['chap_id']))) {
            return LogUtil::registerArgsError();
            ;
        }


// The user API function is called.  This takes the item ID which
// we obtained from the input and gets us the information on the
// appropriate item.  If the item does not exist we post an appropriate
// message and return
        $item = ModUtil::apiFunc('Book', 'user', 'getchapter', array('chap_id' => $args['chap_id']));

        if ($item == false) {
            return false;
        }

// Security check
        if (!SecurityUtil::checkPermission('Book::Chapter', "$args[book_id]::$item[chap_id]", ACCESS_EDIT)) {
            LogUtil::registerPermissionError();
            ;
            return false;
        }

        if (!DBUtil::updateObject($args, 'book_chaps', '', 'chap_id')) {
            return LogUtil::registerError(__('Updating the chapter failed.'));
        }


//Now we have to change the articles if the book id has changed.
        if ($item['book_id'] != $args['book_id']) {
//updateObject only changes the items in the array that have changed. If an item
//is missing in the args array, it is left untouched by the updateObject function
//So, even though our args array only contains book_id and chap_id information,
//it still works, without destroying the contents of the articles
            $pntable = & DBUtil::getTables();
            $artList = &$pntable['book_column'];
            $where = "WHERE $artList[book_id]=" . DataUtil::formatForStore($item['book_id']) .
                    " AND $artList[chap_id]=" . DataUtil::formatForStore($args['chap_id']);

            if (!DBUtil::updateObject($args, 'book', $where, 'art_id')) {
                return LogUtil::registerError(__('Updating the chapter failed.'));
            }
        }
// Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * update an article give the right parameters
     */
    public function updatearticle($args) {
        // Argument check
        if ((!isset($args['book_id'])) ||
                (!isset($args['art_id'])) ||
                (!isset($args['title'])) ||
                (!isset($args['contents'])) ||
                (!isset($args['lang'])) ||
                (!isset($args['next'])) ||
                (!isset($args['prev'])) ||
                (!isset($args['art_number'])) ||
                (!isset($args['chap_id']))) {
            LogUtil::registerArgsError();
            return false;
        }

        // The user API function is called
        $item = ModUtil::apiFunc('Book', 'user', 'getarticle', array('art_id' => $args['art_id']));

        if ($item == false) {
            return false;
        }


        // Security check
        if (!SecurityUtil::checkPermission('Book::Chapter', "$args[book_id]::$args[chap_id]", ACCESS_EDIT)) {
            LogUtil::registerPermissionError();
            return false;
        }

        $pntable = & DBUtil::getTables();
        //delete any highlight data for this article since we just edited it.
        $userList = &$pntable['book_user_data_column'];
        $where = "WHERE $userList[art_id] = '" . DataUtil::formatForStore($args['art_id']) . "'";
        //delete the objects
        if (!DBUtil::DeleteObject(null, 'book_user_data', $where)) {
            //TODO, this has to be checked to make sure its working. Have not done it yet
            return LogUtil::registerError(__('Deleting the highlighting for this object failed.'));
        }

        //now update the article
        if (!DBUtil::updateObject($args, 'book', '', 'art_id')) {
            return LogUtil::registerError(__('Updating the book object failed.'));
        }

        // Let the calling process know that we have finished successfully
        return true;
    }

    public function updatefigure($args) {
// Argument check
//we make sure that all the arguments are there
//and that the chapter is larger than 1
//negative chapters
        if ((!isset($args['fig_id'])) ||
                (!isset($args['fig_number'])) ||
                (!isset($args['chap_number'])) ||
                (!isset($args['book_id'])) ||
                (!isset($args['fig_content'])) ||
                (!isset($args['fig_perm'])) ||
                (!isset($args['fig_title'])) ||
                (!isset($args['img_link']))) {
            LogUtil::registerArgsError();
            return true;
        }

// Security check - important to do this as early on as possible to
// avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            LogUtil::registerPermissionError();
            return false;
        }

        //now update the article
        if (!DBUtil::updateObject($args, 'book_figures', '', 'fig_id')) {
            return LogUtil::registerError(__('Updating the figure failed.'));
        }

// Let any hooks know that we have created a new item.
        ModUtil::callHooks('book', 'updatefigure', $fig_id, 'fig_id');

        return true;
    }

    public function updateglossary($args) {
// Argument check
//we make sure that all the arguments are there
        if ((!isset($args['gloss_id'])) ||
                (!isset($args['term'])) ||
                (!isset($args['definition']))) {
            LogUtil::registerArgsError();
            return true;
        }

// Security check -
//You must have general edit ability to modify the glossary
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            LogUtil::registerPermissionError();
            return false;
        }

        if (!DBUtil::updateObject($args, 'book_glossary', '', 'gloss_id')) {
            return LogUtil::registerError(__('Updating the glossary failed.'));
        }

        // Let any hooks know that we have created a new item.
        ModUtil::callHooks('book', 'updateglossary', $args['gloss_id'], 'gloss_id');

        return true;
    }

    public function createhighlight($args) {
//This is not always working, and I am suspicious of whether this is really working
//I suspect this may be because of puncuation?
//print "uid:$uid art:$art_id, start:$start end:$end";die;
// Argument check
//we make sure that all the arguments are there
//and that the chapter is larger than 1
//negative chapters
        if ((!isset($args['uid'])) ||
                (!isset($args['art_id'])) ||
                (!isset($args['start'])) ||
                (!isset($args['end']))) {
            LogUtil::registerArgsError();
            return false;
        }
//now check to make sure the variables make sense
//none of these is allowed to be less than 0
        if (($args['uid'] < 1) ||
                ($args['art_id'] < 1) ||
                ($args['start'] < 0) ||
                ($args['end']) < 0) {
            LogUtil::registerArgsError();
            return false;
        }


// Security check - important to do this as early on as possible to
// avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::Chapter', "::", ACCESS_READ)) {
            LogUtil::registerPermissionError();
            return false;
        }
//insert a new object. The book_id is inserted into the $args array
        if (!DBUtil::insertObject($args, 'book_user_data')) {
            return LogUtil::registerError(__('Creating that highlight failed.'));
        }

        return true;
    }

    public function deletehighlight($args) {
// Argument check
        if (!isset($args['id'])) {
            LogUtil::registerArgsError();
            return false;
        }

        if (!DBUtil::deleteObjectByID('book_user_data', $args['id'])) {
            return LogUtil::registerError(__('Deleting the highlight failed.'));
        }
        ModUtil::callHooks('item', 'deletehighlight', $id, '');

// Let the calling process know that we have finished successfully
        return true;
    }

    public function getemptyglossaryitems($args) {
//security check
        if (!SecurityUtil::checkPermission('Book::Chapter', ".*::.*", ACCESS_EDIT)) {
            return false;
        }
// Build the where clause
        $pntable = & DBUtil::getTables();
        $bookGlossList = &$pntable['book_glossary_column'];
        $where = "WHERE $bookGlossList[definition]=''";
//$orderby = "ORDER BY $bookGlossList[term]";
        $items = DBUtil::selectObjectArray('book_glossary', $where); //, $orderby, -1, -1, 'term');
// Return the item array
        return $items;
    }

// TODO: Create a search and replace function that will walkthough and change all the articles. 
// You should make it possble to do a preview of the first found article and display it. Have a
// pattern field and a replace field where the data can be entere and a check box for preview.

    public function dosearchreplacebook($args) {
//no securtiy check needed, handled at the chapter level
        if (!isset($args['book_id']) || !isset($args['search_pat']) || !isset($args['replace_pat']) || !isset($args['preview'])) {
            LogUtil::registerArgsError();
            return true;
        }
//grab all the chapters from the book
        $items = ModUtil::apiFunc('Book', 'user', 'getallchapters', array('book_id' => $args['book_id']));
//check to make sure we got something
        if (!$items) {
            LogUtil::registerArgsError();
        }
        $return_text = "";
        foreach ($items as $item) {
//call the serach and replace function on each chapter
            $args['chap_id'] = $item['chap_id'];
            $return_text .= $this->dosearchreplacechap($args);
        }
        return $return_text;
    }

    public function dosearchreplacechap($args) {
        if (!isset($args['chap_id']) || !isset($args['book_id'])
                || !isset($args['search_pat']) || !isset($args['replace_pat'])
                || !isset($args['preview'])) {
            LogUtil::registerArgsError();
            return true;
        }

// Security check - important to do this as early on as possible to
// avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::Chapter', "$args[book_id]::$args[chap_id]", ACCESS_EDIT)) {
            LogUtil::registerPermissionError();
            return false;
        }

        $art_items = ModUtil::apiFunc('Book', 'user', 'getallarticles', array('chap_id' => $args['chap_id']));
        if (!$art_items) {
            LogUtil::registerArgsError();
        }
        $preview_text = "";
        $search_pat = $args['search_pat'];
        $replace_pat = $args['replace_pat'];
        if ($args['preview']) {
            $replace_pat = "<b>$replace_pat</b>";
        }
        foreach ($art_items as $item) {
            $count;
            $old = $item['contents'];
            //this is adding lots of <b> before and after for some reason. Like it is being search more than once time.
            if ($args['preview']) {
                $new = preg_replace($search_pat, $replace_pat, $old, -1, $count);
                if (!isset($new)) {
                    // there is an error in the serach pattern
                    return LogUtil::registerError(__('The search pattern for the regular expression is poorly formed.'));
                }
                if ($count > 0) {
                    $preview_text .= "<h3>" . $item['title'] . "</h3>\n<p>" . $new . "</p>";
                }
            } else {
                $new = preg_replace($search_pat, $replace_pat, $old, -1, $count);
                $item['contents'] = $new;
                if ($count > 0) {
                    if (!DBUtil::updateObject($item, 'book', '', 'art_id')) {
                        return LogUtil::registerError(__('Updating the article failed during Search and Replace function.'));
                    }
                }
            }
        }
        return $preview_text;
        //TODO I am in the midst of debugging this
    }

    public function addglossaryitems($args) {
        $art_id = $args['art_id'];
        //get the article
        $article = ModUtil::apiFunc('Book', 'user', 'getarticle', array('art_id' => $art_id));
        //add glossary terms
        $article['contents'] = book_adminapi_add_glossary_terms(array('in_text' => $article['contents']));
        //save the article
        return ModUtil::apiFunc('book', 'admin', 'updatearticle', $article);
    }

//glossary addition code



    public function add_glossary_terms($args) {
        // Security check - important to do this as early on as possible to
        // avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            LogUtil::registerPermissionError();
            return false;
        }
        //grab the incoming text
        $contents = $args['in_text'];
        //clean out any glossary item that are there.
        $pattern = "/<a class=\"glossary\" href.*?'\)\">(.*?)<\/a>/";
        $contents = preg_replace($pattern, "$1", $contents);

        //separate into key words and contents
        //we do not want to add glossary items to the key terms section
        $matches = array();
        if (preg_match('|(<ul class=\"key_words\">.*?</ul>)(.*)|s', $contents, $matches)) {
            $key_terms = $matches[1];
            $contents = $matches[2];
        }
        //grab all the glossary terms
        $glossary_terms = ModUtil::apiFunc('Book', 'user', 'getallglossary');
        $terms = array();
        //pack them into a list to use in the preg_replace_callback
        foreach ($glossary_terms as $term) {
            //This is a search pattern, hence the slash before and after
            //the \b denotes a word boundary, so the pattern will search for whole words
            //The i signifies case insensitive. we also add 30 characters before and after to
            //check for things we don't want
            //qualify any characters in the gloss definitions.
            $search_string = preg_quote($term['term']);
            $terms[] = "|(.{150}[^>])(\b$search_string\b)(.{150})|is";
        }

        //sort the terms, doing the biggest ones first
        usort($terms, $this->_sort_term_sizes);
        //now search for all the terms
        $result = preg_replace_callback($terms, $this->_glossreplacecallback, $contents, 1);
        if ($result != "") {
            $contents = $result;
        }

        //replace the figures with a link to just the figure
//    $pattern = "/([^\d][^\"][> ])Figure ([0-9]*)-([0-9]*)/";
//    $replacement = "$1<a href=\"" . $URL . "&amp;book_id=$book_id&amp;fig_number=$3&amp;chap_number=$2\">Figure $2-$3</a>";
//    $contents = preg_replace($pattern, $replacement, $contents);

        return $key_terms . $contents . "\n";

//update the object. It knows which one to update from the
//art_id stored in the item
    }

    /**
     * _sort_term_sizes
     * @param $a
     * @param $b
     * @return whether $a is longer than B
     *
     *  Private function called to sort the terms by size. We have to order them this way to prevent
     *  overlapping definitions. For example, if you have defined 'quarter note' and 'note'. You do not
     *  want note defined inside quarter note. The replace callback function, used after this, checks
     *  for this, but to get this to work, you need to order terms by size
     */
    private function _sort_term_sizes($a, $b) {

        $sizea = strlen($a);
        $sizeb = strlen($b);

        if ($sizea == $sizeb) {
            return 0;
        }
        return ($sizea < $sizeb) ? 1 : -1;
    }

    /**
     * _glossreplacecallback
     *
     * @param $matches
     * @return the replacement string
     * The function searches for patterns like (.{30})(glossterm)(.{30}). We scan before and after
     * to make sure that the match does not have class=glossary (this means it is already part of a
     * definition, a heading tag near it, is part of a table, and is not part of a link.
     * Note, right now this was
     */
    private function _glossreplacecallback($matches) {
//serach in the preceeding 30 characters for a glossary tag
//if the tag is there, then we do not want to define this one.
//this prevents overlapping glossary items
//no matches to items that are part of headings
        $pattern = "|</{0,1}h[1-6]>|";
        if (preg_match($pattern, $matches[0])) {
//if we find this in the preceeding string, we just return the whole match.
            return $matches[0];
        }
//no matches in tables
        $pattern = "|</{0,1}td>|";
        if (preg_match($pattern, $matches[0])) {
//if we find this in the preceeding string, we just return the whole match.
            return $matches[0];
        }
//no matches in tables
        $pattern = "|</{0,1}th>|";
        if (preg_match($pattern, $matches[0])) {
//if we find this in the preceeding string, we just return the whole match.
            return $matches[0];
        }
//make sure its not part of a link
        $pattern = "|index.php|";
        if (preg_match($pattern, $matches[0])) {
//if we find this in the preceeding string, we just return the whole match.
            return $matches[0];
        }
        $pattern = "|escape\(|";
        if (preg_match($pattern, $matches[0])) {
//if we find this in the preceeding string, we just return the whole match.
            return $matches[0];
        }
        $pattern = "|\'\)\">|";
        if (preg_match($pattern, $matches[0])) {
//if we find this in the preceeding string, we just return the whole match.
            return $matches[0];
        }

        $ret_text = $matches[1] . "<a class=\"glossary\">" . $matches[2] . "</a>" . $matches[3];

        return $ret_text;
    }

}

?>