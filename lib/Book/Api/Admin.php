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
        if ((!isset($args['name']))) {
            LogUtil::registerArgsError();
            return false;
        }

// Security check - important to do this as early on as possible to
// avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_ADD)) {
            LogUtil::registerPermissionError();
            return false;
        }
//insert a new object. The bid is inserted into the $args array
        if (!DBUtil::insertObject($args, 'name', 'bid')) {
            return LogUtil::registerError(__('Book creation failed.'));
        }

// Return the id of the newly created item to the calling process
        return $bid;
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
        if ((!isset($args['name'])) ||
                (!isset($args['bid']))) {
            LogUtil::registerArgsError();
            return false;
        }


        if (!isset($arts['number']) || ($args['number'] < 1)) {
//we need to generate a chapter number. Count the number of
//chapters and then add a 1 to it. This may fail if a
//chapter is missing, but its not fatal to have two chapters
//with the same number
            $args['number'] = ModUtil::apiFunc('Book', 'user', 'countchapters', array('bid' => $book)) + 1;
        }
// Security check - important to do this as early on as possible to
// avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_ADD)) {
            LogUtil::registerPermissionError();
            return false;
        }

//insert a new object. The bid is inserted into the $args array
        if (!DBUtil::insertObject($args, 'book_chaps', 'cid')) {
            return LogUtil::registerError(_CREATEFAILED);
        }

// Return the id of the newly created item to the calling process
        return $args['cid'];
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
                (!isset($args['bid'])) ||
                (!isset($args['contents'])) ||
                (!isset($args['next'])) ||
                (!isset($args['prev'])) ||
                (!isset($args['aid'])) ||
                (!isset($args['cid'])) || ($args['cid'] < 1) ||
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
//    if(isset($args['aid'])){
//        $preserve = true;
//    }
//insert a new object. The bid is inserted into the $args array
        if (!DBUtil::insertObject($args, 'book', 'aid', $preserve)) {
            return LogUtil::registerError(_CREATEFAILED . "article insert");
        }

// Return the id of the newly created item to the calling process
        return $args['aid'];
    }

    public function createfigure($args) {
// Argument check
//we make sure that all the arguments are there
//and that the chapter is larger than 1
//negative chapters
        if ((!isset($args['fig_number'])) ||
                (!isset($args['number'])) ||
                (!isset($args['content'])) ||
                (!isset($args['title'])) ||
                (!isset($args['perm'])) ||
                (!isset($args['bid'])) ||
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

//insert a new object. The bid is inserted into the $args array
        if (!DBUtil::insertObject($args, 'book_figures', 'fid')) {
            return LogUtil::registerError(_CREATEFAILED . "article insert");
        }

// Return the id of the newly created item to the calling process
        return $args['fid'];
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
        if ((!isset($args['url']))) {
            $args['url'] = "";
        }


// Security check
        if (!SecurityUtil::checkPermission('Book::Chapter', "::", ACCESS_ADD)) {
            LogUtil::registerPermissionError();
            return false;
        }

//insert a new object. The bid is inserted into the $args array
        if (!DBUtil::insertObject($args, 'book_glossary', 'gid')) {
            return LogUtil::registerError(__('Creating the glossary item failed, createglossary'));
        }

// Return the id of the newly created item to the calling process
        return $gid;
    }

    /**
     * delete a book item
     * @param $args['tid'] ID of the item
     * @returns bool
     * @return true on success, false on failure
     */
    public function delete($args) {
// Argument check
        if (!isset($args['bid'])) {
            LogUtil::registerArgsError();
            return false;
        }

// The user API function is called.
        $item = ModUtil::apiFunc('Book', 'user', 'get', array('bid' => $args['bid']));


        if ($item == false) {
            LogUtil::registerPermissionError();
            return false;
        }


// Security check
        if (!SecurityUtil::checkPermission('Book::', "$args[bid]::", ACCESS_DELETE)) {
            LogUtil::registerPermissionError();
            return false;
        }

       if (!DBUtil::deleteObjectByID('name', $args['bid'], 'bid')) {
            return LogUtil::registerError(__('Deleting the book failed'));
        }

        //Now delete all the articles and chapters
        $pntable = & DBUtil::getTables();
            //delete all the articles associated with this book
        $articleList = &$pntable['book_column'];
        $where = "WHERE $articleList[bid] = '" . DataUtil::formatForStore($args['bid']) . "'";
        $articles_of_book = DBUtil::selectObjectArray('book', $where);
        foreach($articles_of_book as $article){
            //we call the module controller to make sure it communicates with any hooked modules.
            ModUtil::func('Book', 'admin', 'deletearticle', array('aid' => $article['aid']));
        }
//delete all the chapters associated with this book
        $chapList = &$pntable['book_chaps_column'];
        $where = "WHERE $chapList[bid] = '" . DataUtil::formatForStore($args['bid']) . "'";

        if (!DBUtil::deleteWhere('book_chaps', $where)) {
            return LogUtil::registerError(__('Deleting the chapters of the book failed'));
        }


// Let the calling process know that we have finished successfully
        return true;
    }

    public function deletechapter($args) {
// Argument check - make sure that all required arguments are present,
// if not then set an appropriate error message and return
        if (!isset($args['cid'])) {
            LogUtil::registerArgsError();
            return false;
        }
// Security check
        if (!SecurityUtil::checkPermission('::Chapter', "::$args[cid]", ACCESS_DELETE)) {
            LogUtil::registerPermissionError();
            return false;
        }
        $pntable = & DBUtil::getTables();
//delete all the articles associated with this chapter
        $articleList = &$pntable['book_column'];
        $where = "WHERE $articleList[cid] = '" . DataUtil::formatForStore($args['cid']) . "'";
        $articles_of_book = DBUtil::selectObjectArray('book', $where);
        foreach($articles_of_book as $article){
            //we call the module controller to make sure it communicates with any hooked modules.
            ModUtil::func('Book', 'admin', 'deletearticle', array('aid' => $article['aid']));
        }
        
//finally delete the chapter
        if (!DBUtil::deleteObjectByID('book_chaps', $args['cid'], 'cid')) {
            return LogUtil::registerError(__('Deleting the chapter failed.'));
        }


// Let the calling process know that we have finished successfully
        return true;
    }

    public function deletearticle($args) {
        // Argument check
        if (!isset($args['aid'])) {
            LogUtil::registerArgsError();
            return false;
        }
        $article = ModUtil::apiFunc('Book', 'user', 'getarticle', array('aid' => $args['aid']));

        // Security check
        if (!SecurityUtil::checkPermission('Book::Chapter', "$article[bid]::$article[cid]", ACCESS_DELETE)) {
            LogUtil::registerPermissionError();
            return false;
        }
       if (!DBUtil::deleteObjectByID('book', $args['aid'], 'aid')) {
            return LogUtil::registerError(__('Deleting the article failed.'));
        }
        

// Let the calling process know that we have finished successfully
        return true;
    }

    public function deletefigure($args) {
// Argument check
        if (!isset($args['fid'])) {
            LogUtil::registerArgsError();
            return false;
        }
//security check
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_DELETE)) {
            LogUtil::registerPermissionError();
            return false;
        }
        if (!DBUtil::deleteObjectByID('book_figures', $args['fid'], 'fid')) {
            return LogUtil::registerError(__('Deleting the figure failed.'));
        }

// Let the calling process know that we have finished successfully
        return true;
    }

    public function deleteglossary($args) {
// Argument check
        if (!isset($args['gid'])) {
            LogUtil::registerArgsError();
            return false;
        }
//security check
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_DELETE)) {
            LogUtil::registerPermissionError();
            return false;
        }
        if (!DBUtil::deleteObjectByID('book_glossary', $args['gid'], 'gid')) {
            return LogUtil::registerError(__('Deleting the glossary item failed.'));
        }

// Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * update a book item
     * @param $args['bid'] the ID of the item
     * @param $args['name'] the new name of the item
     * @param $args['number'] the new number of the item
     */
    public function update($args) {
// Argument check
        if ((!isset($args['bid'])) ||
                (!isset($args['name']))) {
            LogUtil::registerArgsError();
            return false;
        }
        $bid = $args['bid'];
// The user API function is called.  This takes the item ID which
// we obtained from the input and gets us the information on the
// appropriate item.  If the item does not exist we post an appropriate
// message and return
        $item = ModUtil::apiFunc('Book', 'user', 'get', array('bid' => $bid));

        if ($item == false) {
            //we don't throw an error, we depend on the caling funciton to do that.
            return false;
        }

// Security check
        if (!SecurityUtil::checkPermission('Book::Chapter', "$item[bid]::.*", ACCESS_EDIT)) {
            LogUtil::registerPermissionError();
            return false;
        }

        if (!DBUtil::updateObject($args, 'name', '', 'bid')) {
            return LogUtil::registerError(__('Updating the book failed.'));
        }

// Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * update a chapter item
     * @param $args['bid'] the id of the book
     * @param $args['name'] the title of the chapter
     * @param $args['number'] the chapter number
     * @param $args['cid'] the unique ID of the chapter
     */
    public function updatechapter($args) {
// Argument check
        if ((!isset($args['bid'])) ||
                (!isset($args['name'])) ||
                (!isset($args['number'])) ||
                (!isset($args['cid']))) {
            return LogUtil::registerArgsError();
            ;
        }


// The user API function is called.  This takes the item ID which
// we obtained from the input and gets us the information on the
// appropriate item.  If the item does not exist we post an appropriate
// message and return
        $item = ModUtil::apiFunc('Book', 'user', 'getchapter', array('cid' => $args['cid']));

        if ($item == false) {
            return false;
        }

// Security check
        if (!SecurityUtil::checkPermission('Book::Chapter', "$args[bid]::$item[cid]", ACCESS_EDIT)) {
            LogUtil::registerPermissionError();
            ;
            return false;
        }

        if (!DBUtil::updateObject($args, 'book_chaps', '', 'cid')) {
            return LogUtil::registerError(__('Updating the chapter failed.'));
        }


//Now we have to change the articles if the book id has changed.
        if ($item['bid'] != $args['bid']) {
//updateObject only changes the items in the array that have changed. If an item
//is missing in the args array, it is left untouched by the updateObject function
//So, even though our args array only contains bid and cid information,
//it still works, without destroying the contents of the articles
            $pntable = & DBUtil::getTables();
            $artList = &$pntable['book_column'];
            $where = "WHERE $artList[bid]=" . DataUtil::formatForStore($item['bid']) .
                    " AND $artList[cid]=" . DataUtil::formatForStore($args['cid']);

            if (!DBUtil::updateObject($args, 'book', $where, 'aid')) {
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
        if ((!isset($args['bid'])) ||
                (!isset($args['aid'])) ||
                (!isset($args['title'])) ||
                (!isset($args['contents'])) ||
                (!isset($args['lang'])) ||
                (!isset($args['next'])) ||
                (!isset($args['prev'])) ||
                (!isset($args['aid'])) ||
                (!isset($args['cid']))) {
            LogUtil::registerArgsError();
            return false;
        }

        // The user API function is called
        $item = ModUtil::apiFunc('Book', 'user', 'getarticle', array('aid' => $args['aid']));

        if ($item == false) {
            return false;
        }


        // Security check
        if (!SecurityUtil::checkPermission('Book::Chapter', "$args[bid]::$args[cid]", ACCESS_EDIT)) {
            LogUtil::registerPermissionError();
            return false;
        }

        $pntable = & DBUtil::getTables();
        //delete any highlight data for this article since we just edited it.
        $userList = &$pntable['book_user_data_column'];
        $where = "WHERE $userList[aid] = '" . DataUtil::formatForStore($args['aid']) . "'";
        //delete the objects
        if (!DBUtil::DeleteObject(null, 'book_user_data', $where)) {
            //TODO, this has to be checked to make sure its working. Have not done it yet
            return LogUtil::registerError(__('Deleting the highlighting for this object failed.'));
        }

        //now update the article
        if (!DBUtil::updateObject($args, 'book', '', 'aid')) {
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
        if ((!isset($args['fid'])) ||
                (!isset($args['fig_number'])) ||
                (!isset($args['number'])) ||
                (!isset($args['bid'])) ||
                (!isset($args['content'])) ||
                (!isset($args['perm'])) ||
                (!isset($args['title'])) ||
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
        if (!DBUtil::updateObject($args, 'book_figures', '', 'fid')) {
            return LogUtil::registerError(__('Updating the figure failed.'));
        }

        return true;
    }

    public function updateglossary($args) {
// Argument check
//we make sure that all the arguments are there
        if ((!isset($args['gid'])) ||
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

        if (!DBUtil::updateObject($args, 'book_glossary', '', 'gid')) {
            return LogUtil::registerError(__('Updating the glossary failed.'));
        }

        return true;
    }

    public function createhighlight($args) {
//This is not always working, and I am suspicious of whether this is really working
//I suspect this may be because of puncuation?
//print "uid:$uid art:$aid, start:$start end:$end";die;
// Argument check
//we make sure that all the arguments are there
//and that the chapter is larger than 1
//negative chapters
        if ((!isset($args['uid'])) ||
                (!isset($args['aid'])) ||
                (!isset($args['start'])) ||
                (!isset($args['end']))) {
            LogUtil::registerArgsError();
            return false;
        }
//now check to make sure the variables make sense
//none of these is allowed to be less than 0
        if (($args['uid'] < 1) ||
                ($args['aid'] < 1) ||
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
//insert a new object. The bid is inserted into the $args array
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
        if (!isset($args['bid']) || !isset($args['search_pat']) || !isset($args['replace_pat']) || !isset($args['preview'])) {
            LogUtil::registerArgsError();
            return true;
        }
//grab all the chapters from the book
        $items = ModUtil::apiFunc('Book', 'user', 'getallchapters', array('bid' => $args['bid']));
//check to make sure we got something
        if (!$items) {
            LogUtil::registerArgsError();
        }
        $return_text = "";
        foreach ($items as $item) {
//call the serach and replace function on each chapter
            $args['cid'] = $item['cid'];
            $return_text .= $this->dosearchreplacechap($args);
        }
        return $return_text;
    }

    public function dosearchreplacechap($args) {
        if (!isset($args['cid']) || !isset($args['bid'])
                || !isset($args['search_pat']) || !isset($args['replace_pat'])
                || !isset($args['preview'])) {
            LogUtil::registerArgsError();
            return true;
        }

// Security check - important to do this as early on as possible to
// avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::Chapter', "$args[bid]::$args[cid]", ACCESS_EDIT)) {
            LogUtil::registerPermissionError();
            return false;
        }

        $art_items = ModUtil::apiFunc('Book', 'user', 'getallarticles', array('cid' => $args['cid']));
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
                    if (!DBUtil::updateObject($item, 'book', '', 'aid')) {
                        return LogUtil::registerError(__('Updating the article failed during Search and Replace function.'));
                    }
                }
            }
        }
        return $preview_text;
        //TODO I am in the midst of debugging this
    }

    public function addglossaryitems($args) {
        $aid = $args['aid'];
        //get the article
        $article = ModUtil::apiFunc('Book', 'user', 'getarticle', array('aid' => $aid));
        //add glossary terms
        $article['contents'] = $this->add_glossary_terms(array('in_text' => $article['contents']));
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
        usort($terms, array($this, _sort_term_sizes));
        //now search for all the terms
        $result = preg_replace_callback($terms, array($this, _glossreplacecallback), $contents, 1);
        if ($result != "") {
            $contents = $result;
        }

        //replace the figures with a link to just the figure
//    $pattern = "/([^\d][^\"][> ])Figure ([0-9]*)-([0-9]*)/";
//    $replacement = "$1<a href=\"" . $url . "&amp;bid=$bid&amp;fig_number=$3&amp;number=$2\">Figure $2-$3</a>";
//    $contents = preg_replace($pattern, $replacement, $contents);

        return $key_terms . $contents . "\n";

//update the object. It knows which one to update from the
//aid stored in the item
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