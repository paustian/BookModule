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
use Symfony\Component\HttpFoundation\RedirectResponse;
use ModUtil;

class Book_Api_Admin extends Zikula_AbstractApi {

    public function create($args) {
        // Argument check
        if ((!isset($args['name']))) {
            LogUtil::addErrorPopup($this->__('Arguments not set properly in create function'));
            return false;
        }

        // Security check - important to do this as early on as possible to
        // avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_ADD)) {
            LogUtil::registerPermissionError();
            return false;
        }
        //insert a new object. The bid is inserted into the $args array
        $book = new Book_Entity_Book();
        $book->merge($args);
        $this->entityManager->persist($book);
        try {
            $this->entityManager->flush();
        } catch (Zikula_Exception $e) {
            echo "<pre> createbook";
            var_dump($e->getDebug());
            echo "</pre>";
            die;
        }

        // Return the id of the newly created item to the calling process
        return $book->getBid();
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
            LogUtil::addErrorPopup($this->__('Argument error in createchapter.'));
            return false;
        }


        if (!isset($args['number']) || ($args['number'] < 1)) {
            //we need to generate a chapter number. Count the number of
            //chapters and then add a 1 to it. This may fail if a
            //chapter is missing, but its not fatal to have two chapters
            //with the same number
            $args['number'] = ModUtil::apiFunc('Book', 'user', 'countchapters', array('bid' => $args['bid'])) + 1;
        }
        // Security check - important to do this as early on as possible to
        // avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_ADD)) {
            LogUtil::registerPermissionError();
            return false;
        }

        $chapter = new Book_Entity_BookChapters();
        $chapter->merge($args);
        $this->entityManager->persist($chapter);
        try {
            $this->entityManager->flush();
        } catch (Zikula_Exception $e) {
            echo "<pre>createchapter";
            var_dump($e->getDebug());
            echo "</pre>";
            die;
        }

        // Return the id of the newly created item to the calling process
        return $chapter->getCid();
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
        if (!isset($args['prev'])) {
            $args['prev'] = 0;
        }
        if (!isset($args['next'])) {
            $args['next'] = 0;
        }
        if ((!isset($args['title'])) ||
                (!isset($args['bid'])) ||
                (!isset($args['contents'])) ||
                (!isset($args['number'])) ||
                (!isset($args['cid'])) ||
                ($args['cid'] < 1) ||
                (!isset($args['lang']))
        ) {
            LogUtil::addErrorPopup($this->__('Argument error in createarticle.'));
            return false;
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

        $article = new Book_Entity_BookArticles();
        $article->merge($args);
        $this->entityManager->persist($article);
        try {
            $this->entityManager->flush();
        } catch (Zikula_Exception $e) {
            echo "<pre> createarticle";
            var_dump($e->getDebug());
            echo "</pre>";
            die;
        }

        // Return the id of the newly created item to the calling process
        return $article->getAid();
    }

    public function createfigure($args) {
        // Argument check
        //we make sure that all the arguments are there
        //and that the chapter is larger than 1
        //negative chapters
        if ((!isset($args['fig_number'])) ||
                (!isset($args['chap_number'])) ||
                (!isset($args['content'])) ||
                (!isset($args['title'])) ||
                (!isset($args['perm'])) ||
                (!isset($args['bid'])) ||
                (!isset($args['img_link']))) {
            LogUtil::addErrorPopup($this->__('Argument error in createfigure.'));
            return false;
        }
        // Security check - important to do this as early on as possible to
        // avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::Chapter', "::", ACCESS_ADD)) {
            LogUtil::registerPermissionError();
            return false;
        }

        $fig = new Book_Entity_BookFigures();
        $fig->merge($args);
        $this->entityManager->persist($fig);
        try {
            $this->entityManager->flush();
        } catch (Zikula_Exception $e) {
            echo "<pre> createfigure";
            var_dump($e->getDebug());
            echo "</pre>";
            die;
        }

        // Return the id of the newly created item to the calling process
        return $fig->getFid();
    }

    public function createglossary($args) {
        // Argument check
        if ((!isset($args['term'])) ||
                (!isset($args['definition']))) {
            LogUtil::addErrorPopup($this->__('Argument error in createglossary.'));
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
        //First lets see if we can find it
        $repository = $this->entityManager->getRepository('Book_Entity_BookGloss');
        $where = "a.term = '" . DataUtil::formatForStore($args['term']) . "'";
        $item = $repository->getGloss('term', $where);
        $gloss = '';
        if ($item) {
            $gloss = $item[0];
            $gloss->merge($args);
        } else {
            $gloss = new Book_Entity_BookGloss();
            $gloss->merge($args);
            $this->entityManager->persist($gloss);
        }

        try {
            $this->entityManager->flush();
        } catch (Zikula_Exception $e) {
            echo "<pre> createglossary";
            var_dump($e->getDebug());
            echo "</pre>";
            die;
        }

        // Return the id of the newly created item to the calling process
        return $gloss->getGid();
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
            LogUtil::addErrorPopup($this->__('Argument error in delete.'));
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

        //delete all the chapters associated with this book
        //this will in turn delete al the articles
        $where = "a.bid = '" . DataUtil::formatForStore($args['bid']) . "'";
        $repository = $this->entityManager->getRepository('Book_Entity_BookChapters');
        $chaps_of_book = $repository->getChapters('cid', $where);
        foreach ($chaps_of_book as $chap) {
            //we call the module controller to make sure it communicates with any hooked modules.
            ModUtil::apiFunc('Book', 'admin', 'deletechapter', array('cid' => $article['cid']));
        }

        //Now delete the book
        $book = $this->entityManager->getRepository('Book_Entity_Book')->find($item['bid']);
        $this->entityManager->remove($book);
        $this->entityManager->flush();


        // Let the calling process know that we have finished successfully
        return true;
    }

    public function deletechapter($args) {
        // Argument check - make sure that all required arguments are present,
        // if not then set an appropriate error message and return
        if (!isset($args['cid'])) {
            LogUtil::addErrorPopup($this->__('Argument error in deletechapter'));
            return false;
        }
        // Security check
        if (!SecurityUtil::checkPermission('::Chapter', "::$args[cid]", ACCESS_DELETE)) {
            LogUtil::registerPermissionError();
            return false;
        }
        $where = "a.cid = '" . DataUtil::formatForStore($args['cid']) . "'";
        $repository = $this->entityManager->getRepository('Book_Entity_BookArticles');
        $articles_of_book = $repository->getArticles('aid', $where);

        foreach ($articles_of_book as $article) {
            //we call the module controller to make sure it communicates with any hooked modules.
            ModUtil::apiFunc('Book', 'admin', 'deletearticle', array('aid' => $article['aid']));
        }

        //delete the chapter
        $repository = $this->entityManager->getRepository('Book_Entity_BookChapters');
        $chap = $repository->find($args['cid']);
        $this->entityManager->remove($chap);
        $this->entityManager->flush();
        // Let the calling process know that we have finished successfully
        return true;
    }

    public function deletearticle($args) {
        // Argument check
        if (!isset($args['aid'])) {
            LogUtil::addErrorPopup($this->__('Argument error in deletearticle.'));
            return false;
        }
        $article = ModUtil::apiFunc('Book', 'user', 'getarticle', array('aid' => $args['aid']));

        // Security check
        if (!SecurityUtil::checkPermission('Book::Chapter', "$article[bid]::$article[cid]", ACCESS_DELETE)) {
            LogUtil::registerPermissionError();
            return false;
        }
        $repository = $this->entityManager->getRepository('Book_Entity_BookArticles');
        $article = $repository->find($args['aid']);
        $this->entityManager->remove($article);
        $this->entityManager->flush();
        //notify the hook that we deletd the article
        $this->notifyHooks(new Zikula_ProcessHook('book.ui_hooks.articles.process_delete', $aid));

        // Let the calling process know that we have finished successfully
        return true;
    }

    public function deletefigure($args) {
        // Argument check
        if (!isset($args['fid'])) {
            LogUtil::addErrorPopup($this->__('Argument error in deletefigure.'));
            return false;
        }
        //security check
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_DELETE)) {
            LogUtil::registerPermissionError();
            return false;
        }
        //delete the figure
        $repository = $this->entityManager->getRepository('Book_Entity_BookFigures');
        $article = $repository->find($args['fid']);
        $this->entityManager->remove($article);
        $this->entityManager->flush();

        // Let the calling process know that we have finished successfully
        return true;
    }

    public function deleteglossary($args) {
        // Argument check
        if (!isset($args['gid'])) {
            LogUtil::addErrorPopup($this->__('Argument error in deleteglossary.'));
            return false;
        }
        //security check
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_DELETE)) {
            LogUtil::registerPermissionError();
            return false;
        }

        $repository = $this->entityManager->getRepository('Book_Entity_BookGloss');
        $gloss = $repository->find($args['gid']);

        if ($gloss == false) {
            //we don't throw an error, we depend on the caling funciton to do that.
            return false;
        }

        $this->entityManager->remove($gloss);
        $this->entityManager->flush();

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
            LogUtil::addErrorPopup($this->__('Argument error in update.'));
            return false;
        }
        $bid = $args['bid'];
        // Security check
        if (!SecurityUtil::checkPermission('Book::Chapter', "$bid::.*", ACCESS_EDIT)) {
            LogUtil::registerPermissionError();
            return false;
        }

        $repository = $this->entityManager->getRepository('Book_Entity_Book');
        $book = $repository->find($args['bid']);

        if ($book == false) {
            //we don't throw an error, we depend on the caling funciton to do that.
            return false;
        }

        $book->merge($args);
        $this->entityManager->flush();

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
            return LogUtil::addErrorPopup($this->__('Argument error in updatechatper.'));
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

        $item->merge($args);
        $this->entityManager->flush();

        //Now we have to change the articles if the book id has changed.
        if ($item['bid'] != $args['bid']) {
            //updateObject only changes the items in the array that have changed. If an item
            //is missing in the args array, it is left untouched by the updateObject function
            //So, even though our args array only contains bid and cid information,
            //it still works, without destroying the contents of the articles
            $articles = $this->entityManager->getRepository('Book_Entity_BookArticles');

            $articles->updateBookIdForChapter($args['cid'], $args['bid']);
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
            LogUtil::addErrorPopup($this->__('Argument error in updatearticle.'));
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

        //delete any highlight data for this article since we just edited it.
        $userData = $this->entityManager->getRepository('Book_Entity_BookUserData');
        $userData->removeHighlightsForArticle($args['aid']);


        //now update the article
        $repository = $this->entityManager->getRepository('Book_Entity_BookArticles');
        $article = $repository->find($args['aid']);
        $article->merge($args);
        $this->entityManager->flush();

        //notify the hook that we deletd the article
        $this->notifyHooks(new Zikula_ProcessHook('book.ui_hooks.articles.process_edit', $aid));

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
                (!isset($args['chap_number'])) ||
                (!isset($args['bid'])) ||
                (!isset($args['content'])) ||
                (!isset($args['perm'])) ||
                (!isset($args['title'])) ||
                (!isset($args['img_link']))) {
            LogUtil::addErrorPopup($this->__('Argument error in updatefigure.'));
            return true;
        }

        // Security check - important to do this as early on as possible to
        // avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            LogUtil::registerPermissionError();
            return false;
        }

        $repository = $this->entityManager->getRepository('Book_Entity_BookFigures');
        $fig = $repository->find($args['fid']);
        $fig->merge($args);
        $this->entityManager->flush();

        return true;
    }

    public function updateglossary($args) {
        // Argument check
        //we make sure that all the arguments are there
        if ((!isset($args['gid'])) ||
                (!isset($args['term'])) ||
                (!isset($args['definition']))) {
            LogUtil::addErrorPopup($this->__('Argument error in updateglossary.'));
            return true;
        }

        // Security check -
        //You must have general edit ability to modify the glossary
        if (!SecurityUtil::checkPermission('Book::', "::", ACCESS_EDIT)) {
            LogUtil::registerPermissionError();
            return false;
        }

        //update the glossary entry
        $repository = $this->entityManager->getRepository('Book_Entity_BookGloss');
        $gloss = $repository->find($args['gid']);
        $gloss->merge($args);
        $this->entityManager->flush();


        return true;
    }

    public function createhighlight($args) {

        //print "uid:$uid art:$aid, start:$start end:$end";die;
        // Argument check
        //we make sure that all the arguments are there
        //and that the chapter is larger than 1
        //negative chapters
        if ((!isset($args['uid'])) ||
                (!isset($args['aid'])) ||
                (!isset($args['start'])) ||
                (!isset($args['end']))) {
            LogUtil::addErrorPopup($this->__('Argument error in createhighlight.'));
            return false;
        }
        //now check to make sure the variables make sense
        //none of these is allowed to be less than 0
        if (($args['uid'] < 1) ||
                ($args['aid'] < 1) ||
                ($args['start'] < 0) ||
                ($args['end']) < 0) {
            LogUtil::addErrorPopup($this->__('Argument error in createhighlight.'));
            return false;
        }


        // Security check - important to do this as early on as possible to
        // avoid potential security holes or just too much wasted processing
        if (!SecurityUtil::checkPermission('Book::Chapter', "::", ACCESS_READ)) {
            LogUtil::registerPermissionError();
            return false;
        }
        //save the user data
        $userData = new Book_Entity_BookUserData();
        $userData->merge($args);
        $this->entityManager->persist($userData);
        $this->entityManager->flush();

        return true;
    }

    public function deletehighlight($args) {
        // Argument check
        if (!isset($args['udid'])) {
            LogUtil::addErrorPopup($this->__('Argument error in deletehighlight.'));
            return false;
        }

        //Delete the user data
        $highlight = $this->entityManager->getRepository('Book_Entity_BookUserData')->find($args['udid']);
        $this->entityManager->remove($highlight);
        $this->entityManager->flush();
        // Let the calling process know that we have finished successfully
        return true;
    }

    public function getemptyglossaryitems($args) {
        //security check
        if (!SecurityUtil::checkPermission('Book::Chapter', ".*::.*", ACCESS_EDIT)) {
            return false;
        }
        // Build the where clause
        $repository = $this->entityManager->getRepository('Book_Entity_BookGloss');
        $where = 'a.definition = \'\'';
        $items = $repository->getGloss('term', $where);
        // Return the item array
        return $items;
    }

    // TODO: Create a search and replace function that will walkthough and change all the articles. 
    // You should make it possble to do a preview of the first found article and display it. Have a
    // pattern field and a replace field where the data can be entere and a check box for preview.

    public function dosearchreplacebook($args) {
        //no securtiy check needed, handled at the chapter level
        if (!isset($args['bid']) || !isset($args['search_pat']) || !isset($args['replace_pat']) || !isset($args['preview'])) {
            LogUtil::addErrorPopup($this->__('Argument error in dosearchreplacebook.'));
            return true;
        }
        //grab all the chapters from the book
        $items = ModUtil::apiFunc('Book', 'user', 'getallchapters', array('bid' => $args['bid']));
        //check to make sure we got something
        if (!$items) {
            LogUtil::addErrorPopup($this->__('Argument error in dosearchreplacebook.'));
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
        if (!isset($args['cid']) || !isset($args['bid']) || !isset($args['search_pat']) || !isset($args['replace_pat']) || !isset($args['preview'])) {
            LogUtil::addErrorPopup($this->__('Argument error in dosearchreplacechap.'));
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
            LogUtil::addErrorPopup($this->__('Argument error in dosearchreplacechap. Could not find articles'));
        }
        $preview_text = "";
        $search_pat = $args['search_pat'];

        $replace_pat = $args['replace_pat'];
        if ($args['preview']) {
            $replace_pat = "<b>$replace_pat</b>";
        }
        $repository = $this->entityManager->getRepository('Book_Entity_BookArticles');
                    
        foreach ($art_items as $item) {
            $count;
            $old = $item['contents'];
            //this is adding lots of <b> before and after for some reason. Like it is being search more than once time.
            if ($args['preview']) {
                $new = preg_replace($search_pat, $replace_pat, $old, -1, $count);
                if (!isset($new)) {
                    $search_pat = '~' . $search_pat . '~';
                    $new = preg_replace($search_pat, $replace_pat, $old, -1, $count);
                    if (!isset($new)) {
                        // there is an error in the serach pattern
                        return LogUtil::addErrorPopup($this->__('The search pattern for the regular expression is poorly formed.'));
                    }
                }
                if ($count > 0) {
                    $preview_text .= "<h3>" . $item['title'] . "</h3>\n<p>" . $new . "</p>";
                }
            } else {
                $new = preg_replace($search_pat, $replace_pat, $old, -1, $count);
                if (!isset($new)) {
                    $search_pat = '~' . $search_pat . '~';
                    $new = preg_replace($search_pat, $replace_pat, $old, -1, $count);
                    if (!isset($new)) {
                        // there is an error in the serach pattern
                        return LogUtil::addErrorPopup($this->__('The search pattern for the regular expression is poorly formed.'));
                    }
                }
                $item['contents'] = $new;
                if ($count > 0) {
                    //get the new article and merge the new data
                    $article = $repository->find($item['aid']);
                    $item_array = array();
                    $item_array['aid'] = $item['aid'];
                    $item_array['title'] = $item['title'];
                    $item_array['cid'] = $item['cid'];
                    $item_array['bid'] = $item['bid'];
                    $item_array['contents'] = $item['contents'];
                    $item_array['counter'] = $item['counter'];
                    $item_array['lang'] = $item['lang'];
                    $item_array['next'] = $item['next'];
                    $item_array['prev'] = $item['prev'];
                    $item_array['number'] = $item['number'];
                    
                    //Now fixed. You cannot call this function with an object, it must be an array
                    //and you cannot just cast it, it does funky things. So I had to manuall copy it.
                    $article->merge($item_array);
                }
            }
        }
        $this->entityManager->flush();
        return $preview_text;
    }

    public function addglossaryitems($args) {
        $aid = $args['aid'];
        //get the article
        $article = ModUtil::apiFunc('Book', 'user', 'getarticle', array('aid' => $aid));
        //add glossary terms
        $article['contents'] = $this->add_glossary_terms(array('in_text' => $article['contents']));
        //save the article, we can't just pass article because it is a object 
        //and does not traslate well to an array
        return ModUtil::apiFunc('book', 'admin', 'updatearticle', array('aid' => $article['aid'],
                    'title' => $article['title'],
                    'cid' => $article['cid'],
                    'bid' => $article['bid'],
                    'contents' => $article['contents'],
                    'counter' => $article['counter'],
                    'lang' => $article['lang'],
                    'next' => $article['next'],
                    'prev' => $article['prev'],
                    'number' => $article['number']));
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

        return $key_terms . $contents . "\n";
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