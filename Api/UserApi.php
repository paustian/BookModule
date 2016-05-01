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

use LogUtil;
use SecurityUtil;

class UserApi extends Zikula_AbstractApi {

    /**
     * shorttoc
     * given an article id, derrive the interface for the book tools block
     * 
     * @param type $aid
     * @return type
     */
    public function shorttoc($aid) {

        if (!is_numeric($aid)) {
            LogUtil::addErrorPopup(__('There shorttoc called with no article id.'));
        }

        // The API function is called.  The arguments to the function are passed in
        // as their own arguments array
        $repo = $this->_em->getRepository('PaustianBookModule:BookChaptersEntity');
        $article = $this->_em->getRepository('PaustianBookModule:BookArticlesEntity')->find($aid);
        $chapters = $repo->getChapters($article->getBid());
        // The return value of the function is checked here, and if the function
        // suceeded then an appropriate message is posted.
        if (!$chapters) {
            return LogUtil::addWarningPopup(__('There are no chapters.'));
        }


        foreach ($chapters as $chapter_item) {
            $cid = $chapter_item->getCid();
            if ($chapter_item['number'] > 0) {
                if ($this->hasPermission('Book::Chapter', "$bid::$cid", ACCESS_OVERVIEW)) {
                    $chapName = $this->myTruncate2($chapter_item->getName(), 22);
                    $chapter_data[] = $chapter_item;
                }
            }
        }

        return $this->render('PaustianBookModule:User:book_user_shorttoc.html.twig', ['chapters' => $chapters,
                    'aid' => $aid,
                    'isLoggedIn' => UserUtil::isLoggedIn()]);
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
            LogUtil::addErrorPopup($this->__('bid not set in userapi_get'));
            return false;
        }
        // create a empty result set
        $repository = $this->entityManager->getRepository('Book_Entity_Book');

        $book = $repository->find($bid);

        if ($book === false) {
            LogUtil::addWarningPopup($this->__('There are no books defined. Create a book first.'));
            return false;
        }

        //now check permisions
        $this->throwForbiddenUnless($this->hasPermission('Book::', "$bid::", ACCESS_READ), LogUtil::getErrorMsgPermission());
        // Return the items
        return $book;
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

        $chapters = array();
        $repository = $this->entityManager->getRepository('Book_Entity_BookChapters');
        if ($all_chapters) {
            $chapters = $repository->getChapters();
        } else {
            $where = "a.bid = '" . DataUtil::formatForStore($args['bid']) . "'";
            $chapters = $repository->getChapters('number', $where);
        }
        if ($chapters === false) {
            return LogUtil::addErrorPopup($this->__("Getting chapters failed"));
        }
        $ret_items = array();
        //now check permissions on chapters
        
        foreach ($chapters as $chap) {
            $chapID = $chap->getCid();
            $bookID = $chap->getBid();
            //now check permisions
            if ($this->hasPermission('Book::', "$bookID::$chapID", ACCESS_READ)) {
                $ret_items[] = $chap;
            }
        }
        // Return the item array
        return $ret_items;
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
            LogUtil::addErrorPopup($this->__('Argument error in getchapter.'));
            return false;
        }

        $repository = $this->entityManager->getRepository('Book_Entity_BookChapters');
        $chap = $repository->find($args['cid']);

        if ($chap == false) {
            //we don't throw an error, we depend on the caling funciton to do that.
            return false;
        }

        if ($chap === false) {
            return LogUtil::addErrorPopup($this->__('Unable to get chapter.'));
        }
        // Return the items
        return $chap;
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
            LogUtil::addErrorPopup($this->__('Argument error in getallarticles.'));
            return false;
        }
        //build where clause
        $where = "a.cid = '" . DataUtil::formatForStore($cid) . "'";

        $repository = $this->entityManager->getRepository('Book_Entity_BookArticles');
        $articles = $repository->getArticles('number', $where);

        //workout the acess level
        $access = ACCESS_OVERVIEW;
        if ($get_content) {
            $access = ACCESS_READ;
        }
        $items = array();
        foreach ($articles as $article) {
            if ($this->hasPermission('Book::', $article['bid'] . "::" . $article['cid'], $access)) {
                $items[] = $article;
            }
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
            LogUtil::addErrorPopup($this->__('Error no article id for getarticle'));
            return false;
        }

        $repository = $this->entityManager->getRepository('Book_Entity_BookArticles');
        $item = $repository->find($aid);

        //make sure we have access.
        if (!$this->hasPermission('Book::', $item['bid'] . "::" . $item['cid'], ACCESS_READ)) {
            return false;
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
        if (!isset($args['number']) || !isset($args['cid'])) {
            LogUtil::addErrorPopup($this->__('getarticlebyartnumber argument error'));
            return false;
        }

        if (!$this->hasPermission('Book::', ".*::" . $item['cid'], ACCESS_READ)) {
            return false;
        }

        $where = "a.cid = " . DataUtil::formatForStore($args['cid']) . " AND a.number = " . DataUtil::formatForStore($args['number']);
        $repository = $this->entityManager->getRepository('Book_Entity_BookArticles');
        $article = $repository->getArticles('', $where);

        if ($article === false) {
            return LogUtil::addErrorPopup($this->__('Article get failed in getarticlebyartnumber.'));
        }
        // Return the items
        return $article[0];
    }

    public function getallfigures($args) {
        $bid = $args['bid'];
        $items = array();
        //set up the where clause and check permissions
        if (isset($bid)) {
            if (!$this->hasPermission('Book::', $bid . "::.*", ACCESS_OVERVIEW)) {
                return false;
            }
            $where = "a.bid = '" . DataUtil::formatForStore($bid) . "'";
        } else {
            if (!$this->hasPermission('Book::', ".*::.*", ACCESS_OVERVIEW)) {
                return false;
            }
        }
        $item = $this->entityManager->getRepository('Book_Entity_BookFigures')->getFigures('chap_number', $where);

        // Return the item array
        return $item;
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
        if (!$this->hasPermission('Book::Chapter', ".*::.*", ACCESS_READ)) {
            return DataUtil::formatForDisplayHTML($this->__('You do not have permission to view that book.'));
        }
        $repository = $this->entityManager->getRepository('Book_Entity_BookGloss');
        
        if ($get_defs) {
            $items = $repository->getGloss('term');
        } else {
            $columns = array('gid', 'term');
            $items = $repository->getGloss('term', '', $columns);
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
        
        $repository = $this->entityManager->getRepository('Book_Entity_BookGloss');
        $qb = $repository->createQueryBuilder('a');
        $qb->where("a.term LIKE '%$term%'");
        $query = $qb->getQuery();
        $item = $query->getResult();
        
        if ($items === false) {
            return LogUtil::addErrorPopup($this->__('Unable to find glossary term in findglossaryterm.'));
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
            LogUtil::addErrorPopup($this->__('Argument error in getglossary.'));
            return false;
        }
        //general permission check. You have to be able to access the book, but we don't need a permission filter
        if (!$this->hasPermission('Book::Chapter', ".*::.*", ACCESS_READ)) {
            return DataUtil::formatForDisplayHTML(__("You do not have permission to view that book."));
        }
        $item = array();
        // Get item
        $repository = $this->entityManager->getRepository('Book_Entity_BookGloss');
        if (isset($gid)) {
            $item = $repository->find($gid);
        } else {
            $where = "a.user = '" . DataUtil::formatForStore($user) . "'";
            $item = $repository->getGloss('gid', $where);
        }
        //check for errors
        if ($item === false) {
            return LogUtil::addErrorPopup($this->__('Unable to get in getglossary.'));
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
                LogUtil::addErrorPopup($this->__('Variable error getfigure'));
                return false;
            }
            $fig_number = $args['fig_number'];
            $chap_number = $args['chap_number'];
            $bid = $args['bid'];
        } else {
            $fid = $args['fid'];
        }

        
        $item = array();
        // Get all the information on the item.
        $repository = $this->entityManager->getRepository('Book_Entity_BookFigures');
        if (isset($fid)) {
            $item = $repository->find($fid);
        } else {
            $where = "a.fig_number ='" . DataUtil::formatForStore($fig_number) . "'" .
                    " AND a.chap_number ='" . DataUtil::formatForStore($chap_number) . "'" .
                    " AND  a.bid ='" . DataUtil::formatForStore($bid) . "'";
            //This should pick out a unqiue item
            $result = $repository->getFigures('fid', $where);
            $item = $result[0];
        }
        if ($item === false) {
            return LogUtil::addErrorPopup($this->__('getfigure failed.'));
        }
        
        return $item;
    }

    /**
      /**
     * utility function to count the number of items held by this module
     * @returns integer
     * @return number of items held by this module
     */
    public function countitems() {
        return $this->entityManager->getRepository('Book_Entity_Book')->countBooks();
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
        $where = "a.bid = '" . DataUtil::formatForStore($bid) . "'";
        $count = $this->entityManager->getRepository('Book_Entity_BookChapters')->countChapters($where);
        return $count;
    }

}

