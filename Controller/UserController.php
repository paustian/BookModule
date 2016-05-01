<?php

// pnuser.php,v 1.18 2007/03/16 01:58:56 paustian Exp
// ----------------------------------------------------------------------
// PostNuke Content Management System
// Copyright (C) 2002 by the PostNuke Development Team.
// http://www.postnuke.com/
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
// Purpose of file:  Book user display functions
// ----------------------------------------------------------------------

namespace Paustian\BookModule\Controller;

use Zikula\Core\Controller\AbstractController;
use Zikula\Core\RouteUrl;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use SecurityUtil;
use DataUtil;
use UserUtil;
use ModUtil;
use LogUtil;
use System;
use Paustian\BookModule\Entity\BookEntity;
use Paustian\BookModule\Entity\BookArticlesEntity;
use Paustian\BookModule\Entity\BookFiguresEntity;
use Paustian\BookModule\Entity\BookChaptersEntity;

class UserController extends AbstractController {

    private $maxpixels = 595;

    /**
     * @Route("")
     * 
     * @param $request
     * @return Response
     */
    public function indexAction(Request $request) {
// Security check
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_READ)) {
            throw new AccessDeniedException(__('You do not have pemission to access any books.'));
        }

        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookEntity');
        $books = $repo->getBooks();

        return $this->render('PaustianBookModule:User:book_user_books.html.twig', ['books' => $books]);
    }

    /**
     * @Route("/toc/{book}")
     * 
     * @param Request $request
     * @param BookEntity $book
     * @return type
     */
    public function tocAction(Request $request, BookEntity $book = null) {
        $response = $this->redirect($this->generateUrl('paustianbookmodule_user_index'));
        //if there are no books redirect to the index interface.
        if ($book == null) {
            return $response;
        }
        $chatperids = null;
        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookEntity');
        $booktoc = $repo->buildtoc($book->getBid(), $chapterids);

        //we can simplifiy this quite a bit since we only need 1 book.
        $bookData = $booktoc[0];

        //We need to walk the chapters and elminate the ones that you cannot read
        foreach ($bookData['chapters'] as &$chapter) {
            if ($chapter['number'] < 0) {
                $chapter['print'] = 0; //don't show it.
                continue;
            }
            if ($this->hasPermission($this->name . '::Chapter', $bookData['bid'] . '::' . $chapter['cid'], ACCESS_READ)) {
                $chapter['print'] = 1; //show it and have link to the item
            } else {
                $chapter['print'] = 2; //show it, but no link
            }
        }

        return $this->render('PaustianBookModule:User:book_user_toc.html.twig', ['book' => $bookData]);
    }

// Original PHP code by Chirp Internet: www.chirp.com.au
// Please acknowledge use of this code by including this header.

    function myTruncate2($string, $limit, $break = " ", $pad = "...") {
// return with no change if string is shorter than $limit
        if (strlen($string) <= $limit)
            return $string;

        $string = substr($string, 0, $limit);
        if (false !== ($breakpoint = strrpos($string, $break))) {
            $string = substr($string, 0, $breakpoint);
        }

        return $string . $pad;
    }

    /**
     * @Route("/view")
     * @param Request $request
     * @return type
     */
    public function viewAction(Request $request) {
        return $this->redirect($this->generateUrl('paustianbookmodule_user_index'));
    }

    /**
     * @Route("/display")
     * 
     */
    public function displayAction(Request $request) {
        return $this->redirect($this->generateUrl('paustianbookmodule_user_index'));
    }

    /**
     * @Route("/displayarticle/{article}")
     * 
     * @param Request $request
     * @param BookArticlesEntity $article
     * @return type
     */
    public function displayarticleAction(Request $request, BookArticlesEntity $article = null, $doglossary = true) {
        if (null === $article) {
            $aid = $request->get('aid');
            if (isset($aid)) {
                $article = $this->getDoctrine()->getRepository('PaustianBookModule:BookArticlesEntity')->find($aid);
            }
            if (null === $article) {
                return $this->redirect($this->generateUrl('paustianbookmodule_user_index'));
            }
        }
        //get the chapter title
        $cid = $article->getCid();
        $aid = $article->getAid();
        if (!$this->hasPermission('Book::Chapter', $article->getBid() . "::$cid", ACCESS_READ)) {
            throw new AccessDeniedException(__('You do not have pemission to this chapter.'));
        }
        $doc = $this->getDoctrine();
        $chapter = $doc->getRepository('PaustianBookModule:BookChaptersEntity')->find($cid);


        $content = $article->getContents();
        //Now add the highlights if necessary
        $uid = UserUtil::getVar('uid');
        if ($uid != "") {
            $highlights = $doc->getRepository('PaustianBookModule:BookUserDataEntity')->getHighlights($uid, $aid);
            if ($highlights) {
                $content = $this->_process_highlights($highlights, $content);
            }
        }

        //this code is used for the hook
        $return_url = new RouteUrl($this->generateUrl('paustianbookmodule_user_displayarticle', array('aid' => $aid)));

        //call the user api to increment the counter
        $doc->getRepository('PaustianBookModule:BookArticlesEntity')->incrementCounter($article);

        $show_internals = false;
        if ($this->hasPermission('Book::Chapter', $article->getBid() . "::$cid", ACCESS_EDIT)) {
            $show_internals = true;
        }
        
        $return_text = $this->render('PaustianBookModule:User:book_user_displayarticle.html.twig', ['article' => $article,
            'chapter' => $chapter,
            'content' => $content,
            'return_url' => $return_url,
            'show_internals' => $show_internals]);

        $return_text = $this->addfigures($return_text);

        //work in the glossary items
        if ($doglossary) {
            $return_text = $this->_add_glossary_defs($return_text);
        }

        return new Response($return_text);
    }

//book_user_addfigures
//I factored this out of the above so that I could call it from the admin
//code for exporting the chapters.
    public function addfigures($ioText) {
        //substitute all the figures
        //this is a legacy pattern
        $pattern = "|<!--\(Figure ([0-9]{1,2})-([0-9]{1,3})-([0-9]{1,3})\)-->|";
        $ioText = preg_replace_callback($pattern, array(&$this, "_inlinefigures"), $ioText);

        $pattern = "|{Figure ([0-9]{1,2})-([0-9]{1,3})-([0-9]{1,3}).*}|";
        $ioText = preg_replace_callback($pattern, array(&$this, "_inlinefigures"), $ioText);
        return $ioText;
    }

    /**
     * book_add_glossary_defs
     *
     * Given some text, insert the glossary definitions.
     *
     * @param inText - the text to add glossary definitions to
     * @return retText - the text with glossary definitions addeed
     *
     */
    private function _add_glossary_defs($in_text) {
        //all the work is done in this funcion
        $pattern = "|<a class=\"glossary\">(.*?)</a>|";
        $ret_text = preg_replace_callback($pattern, array(&$this, "_glossary_add"), $in_text);

        return $ret_text;
    }

    /**
     *  glossary_add
     * This is a callback function to convert glossary terms into their definitions.
     * I am added it here and not into the text to keep from polluting the text 
     * with glossary definitions.
     * 
     * @param $matches
     * @return the match text to insert 
     */
    private function _glossary_add($matches) {
        $term = $matches[1];
        $item = array();

        $where['cond'] = "u.term=:term";
        $where['paramkey'] = 'term';
        $where['paramval'] = DataUtil::formatForStore($term);

        $item = $this->getDoctrine()->getRepository('PaustianBookModule:BookGlossEntity')->getGloss('', null, $where);

        if ($item === false) {
            //This did not work, try searching for match instead
            //->where('a.title LIKE :title')
            //   ->setParameter('title', '%'.$data['search'].'%')
            $where['cond'] = "u.term LIKE ?1";
            $where['paramkey'] = '1';
            $where['paramval'] = '%' . DataUtil::formatForStore($term) . '%';
            $item = $this->getDoctrine()->getRepository('PaustianBookModule:BookGlossEntity')->getGloss('', null, $where);
        }
        // Check for an error and if so
        //just return. This is not an error, we just won't replace it
        //$matches[0] contains the found string, so we just return the found
        //string.
        if ($item === false) {
            return $matches[0];
        }
        $definition = $item[0]['definition'];
        $lcterm = strtolower($term);
        $url = DataUtil::formatForDisplayHTML(ModUtil::url('Book', 'user', 'displayglossary')) . "#$lcterm";
        $ret_text = "<a class=\"glossary\" href=\"$url\" title=\"$definition\">$term</a>";
        return $ret_text;
    }

    /**
     * process_highlights
     *
     * Add highlight to the incomping text, based upon the offsets in the highlights array
     *
     */
    private function _process_highlights($highlights, $return_text) {//A modifier that has to go in to account for
        //inserted <span> tags from other highlighting.
        $adjust = 0;
        foreach ($highlights as $hItem) {
            $mid_text = substr($return_text, $hItem['start'] + $adjust, $hItem['end'] - $hItem['start']);
            //search first for <p> tags and add a <p><span> tag
            //note that $ps_count is the number of times it was replaced.
            //print "<b>text before:</b> $mid_text <br />";
            $pattern = '/(<p>)/';
            $replacement = '$1<span class="highlight">';
            $matches = array();
            $ps_count = 0;
            $ps_count = preg_match_all($pattern, $mid_text, $matches);
            if ($ps_count != 0) {
                $mid_text = preg_replace($pattern, $replacement, $mid_text);
            }
            //print "<b>count:</b>$pscount <br /><b>text after open paragraph tag:</b> $mid_text <br />";
            //now search for </p> and add </span></p> tags
            $pe_count = 0;
            $pattern = '/<\/p>/';
            $replacement = "</span></p>";
            $pe_count = preg_match_all($pattern, $mid_text, $matches);
            if ($pe_count != 0) {
                $mid_text = preg_replace($pattern, $replacement, $mid_text);
            }
            //print "<b>text after close paragraph tag:</b> $mid_text <br />";die;

            $return_text = substr($return_text, 0, $hItem['start'] + $adjust) . "<span class=\"highlight\">" .
                    $mid_text . "</span>" .
                    substr($return_text, $hItem['end'] + $adjust, strlen($return_text) - $hItem['end']);
            $adjust += 31 + (24 * $ps_count) + (7 * $pe_count);
        }
        //I need to add a little form on the end for pages that have highlight.
        //This form would contain the id of the php item
        return $return_text;
    }

    public function _inlinefigures($matches) {
        $book_number = $matches[1];
        $chap_number = $matches[2];
        $fig_number = $matches[3];

        //grab the width and heigh if present. The synthax to use here is
        //4-26-1,640,480 the second number is the width, the third is the height
        $pieces = explode(',', rtrim($matches[0], "}"));
        if (count($pieces) > 1) {
            $width = $pieces[1];
            $height = $pieces[2];
        }
        if (!isset($width)) {
            $width = 0;
        }
        if (!isset($height)) {
            $height = 0;
        }

        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookFiguresEntity');
        $figure = $repo->findFigure($fig_number, $chap_number, $book_number);

        $figureText = $this->_renderFigure($figure, $width, $height, false);

        return $figureText;
    }

    /**
     * @Route("/displayfigure/{figure}")
     * 
     * @param Request $request
     * @param \Paustian\BookModule\Controller\BookFiguresEntity $figure
     * @return type
     */
    public function displayfigureAction(Request $request, BookFiguresEntity $figure = null) {

        if (null === $figure) {
            return $this->redirect($this->generateUrl('paustianbookmodule_user_index'));
        }
        if (!$this->hasPermission('Book::', $figure->getBid() . "::", ACCESS_OVERVIEW)) {
            return LogUtil::registerPermissionError();
        }

        $figureText = $this->_renderFigure($figure);
        return $figureText;
    }

    private function _renderFigure(BookFiguresEntity $figure, $width = 0, $height = 0, $stand_alone = false) {
//check to see if we have permission to use the figure
        if ($figure->getPerm() != 0) {
            $visible_link = $this->_buildlink($figure->getImgLink(), $figure->getTitle(), $width, $height, true, false, true, $stand_alone);
        } else {
            $visible_link = __("This figure cannot be displayed because permission has not been granted yet.");
        }

        return $this->render('PaustianBookModule:User:book_user_displayfigure.html.twig', ['figure' => $figure,
                    'visible_link' => $visible_link]);
    }

    private function _buildlink($link, $title = "", $width = 0, $height = 0, $controller = "true", $loop = "false", $autoplay = "true", $stand_alone = false) {
        //if it is a image link, then set it up, else trust that the user
        //has set it up with the right tags.
        $alt_link = preg_replace("|<.*?>|", "", $title);
        $ret_link = "nothing";

        if (strstr($link, ".html")) {
            $file_link = fopen($link, "r");
            $ret_link = fread($file_link, filesize($link));
            fclose($file_link);
        } else
        if ((strstr($link, ".gif")) || (strstr($link, ".jpg")) || (strstr($link, ".png"))) {
            //This was added to prevent failures on file missing. For some reason getimagesize sometimes throws 
            //an error, even though the path to the file is correct
            $docRoot = System::serverGetVar('DOCUMENT_ROOT');
            $test_link = $docRoot . $link;
            if (file_exists($test_link)) {
                $image_data = getimagesize($test_link);
                if ($width == 0) {
                    $width = $image_data[0];
                }
                if ($height == 0) {
                    $height = $image_data[1];
                }
                //if the image is too wide, then shrink it to be no larger than max pixels.
                if (!$stand_alone && $width > $this->maxpixels) {
                    $height = round($height * $this->maxpixels / $width);
                    $width = $this->maxpixels;
                }
                $ret_link = "<p class=\"image\"><img class=\"image\" src=\"" . $link . "\" width=\"" . $width . "\" height=\"" . $height . "\" alt=\"" . $alt_link . "\" /></p>";
            } else {
                $ret_link = "<p class=\"image\"><img class=\"image\" src=\"" . $link . "\" alt=\"" . $alt_link . "\"/></p>";
            }
        } else
        if (strstr($link, ".mov")) {
            if (($width == 0) || ($height == 0)) {
                $width = 320;
                $height = 336;
            }
            $ret_link = "<p class=\"image\"><object data=\"$link\" width=\"$width\"
				        height=\"$height\">
				        <param name=\"movie\" value=\"$link\" />
				        </object></p>";
        } else
        if (strstr($link, ".swf")) {
            if (($width == 0) || ($height == 0)) {
                $image_data = getimagesize($link);
                $width = $image_data[0];
                $height = $image_data[1];
            }
            $ret_link = "<object type=\"application/x-shockwave-flash\" data=\"$link\" width=\"$width\" height=\"$height\">
            <param name=\"movie\" value=\"$link\" /></object>";
        } else {
            $ret_link = $link;
        }
        return $ret_link;
    }

    /**
     * @Route("/displayglossary")
     * 
     * @param Request $request
     * @return type
     */
    public function displayglossaryAction(Request $request) {
//you must have permission to read some book.
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_READ)) {
            throw new AccessDeniedException(__('You do not have pemission to access any glossry items.'));
        }

        $repo = $this->getDoctrine()->getRepository('PaustianBookModule:BookGlossEntity');
        $gloss_data = $repo->getGloss('', ['col' => 'u.term', 'direction' => 'ASC']);

        return $this->render('PaustianBookModule:User:book_user_glossary.html.twig', ['glossary' => $gloss_data]);
    }

    /**
     * @Route("/displaybook/{book}")
     * Display the entire book. Note this should only be called locally and never online. It's a memory hog.
     * The purpose is to just get all of this.
     * @param Request $request
     * @param BookEntity $book
     * @return string
     */
    public function displaybookAction(Request $request, BookEntity $book) {
        if (null === $book) {
            return $this->redirect($this->generateUrl('paustianbookmodule_user_index'));
        }
        $bid = $book->getBid();
        if (!$this->hasPermission($this->name . '::', "$bid::", ACCESS_READ)) {
            throw new AccessDeniedException(__('You do not have pemission to access this book.'));
        }


        $return_text = "";
        $chapRepo = $this->getDoctrine()->getRepository('PaustianBookModule:BookChaptersEntity');
        $chapters = $chapRepo->getChapter($bid);
        //now iterate through each chapter and call display_chapter
        $chapters = ModUtil::apiFunc('Book', 'user', 'getallchapters', array('bid' => $bid));
        foreach ($chapters as $chap_item) {
            $cid = $chap_item->getCid();
            if ($this->hasPermission('Book::Chapter', "$bid::$cid", ACCESS_READ)) {
                $ret_text = $ret_text . $this->displaychapterAction($request, $chapter);
            }
        }
        return $ret_text;
    }

    /**
     * @Route("/displaychapter/{chapter}")
     * 
     * @param Request $request
     * @param BookChaptersEntity $chapter
     * @return type
     */
    public function displaychapterAction(Request $request, BookChaptersEntity $chapter = null) {
        if (null === $chapter) {
            return $this->redirect($this->generateUrl('paustianbookmodule_user_index'));
        }
        $cid = $chapter->getCid();
        $bid = $chapter->getBid();
        //grab the chapter data
        if (!$this->hasPermission('Book::Chapter', "$bid::$cid", ACCESS_READ)) {
            throw new AccessDeniedException(__('You do not have pemission to access the contents of this chapter.'));
            ;
        }
        $artRepo = $this->getDoctrine()->getRepository('PaustianBookModule:BookArticlesEntity');

        //grab all the articles and arrange them by number and we do not need the content.
        $articles = $artRepo->getArticles($cid, true, false);

        //process all inline figures.
        return $this->render('PaustianBookModule:User:book_user_displaychapter.html.twig', ['chapter' => $chapter,
                    'articles' => $articles]);
    }

    /**
     * @Route("/displayarticlesinchapter/{chapter}")
     * 
     * @param Request $request
     * @param BookChaptersEntity $chapter
     * @return type
     */
    public function displayarticlesinchapterAction(Request $request, BookChaptersEntity $chapter = null) {
        if (null === $chapter) {
            $cid = $request->get('cid');
            if (isset($cid)) {
                $chapter = $this->getDoctrine()->getRepository('PaustianBookModule:BookChaptersEntity')->find($cid);
            }
            if (null === $chapter) {
                return $this->redirect($this->generateUrl('paustianbookmodule_user_index'));
            }
        }
        $cid = $chapter->getCid();
        $bid = $chapter->getBid();
        //grab the chapter data
        if (!$this->hasPermission('Book::Chapter', "$bid::$cid", ACCESS_READ)) {
            throw new AccessDeniedException(__('You do not have pemission to access the contents of this chapter.'));
            ;
        }
        $artRepo = $this->getDoctrine()->getRepository('PaustianBookModule:BookArticlesEntity');

        //grab all the articles and arrange them by number and we need the content.
        //Because we want the whole object so we can iterate it.
        $articles = $artRepo->getArticles($cid, true, true);


        //since we are viewing each article, increment the counter.
        foreach ($articles as $article_item) {
            $artRepo->incrementCounter($article_item);
        }

        //process all inline figures.
        $return_text = $this->render('PaustianBookModule:User:book_user_displayarticlesinchapter.html.twig', ['chapter' => $chapter,
            'articles' => $articles]);
        $return_text = $this->addfigures($return_text);
        $return_text = $this->_add_glossary_defs($return_text);
        return new Response($return_text);
    }

    /**
     * @Route("/dodef/")
     * 
     * Given a word or two, check to see if it is in the glossary
     * and if not add it.
     * 
     * @param Request $request
     * @return type
     */
    public function dodefAction(Request $request) {
        return $this->redirect($this->generateUrl('paustianbookmodule_user_index'));
        /*
          $term = FormUtil::getPassedValue('text', isset($args['text']) ? $args['text'] : null);

          //some quick checks

          $comment = "";

          if ($term == "") {
          $comment = __("No word was selected");
          } else {

          if (str_word_count($term) <= 3) {
          if (UserUtil::isLoggedIn()) {
          $url = pnServerGetVar('HTTP_REFERER');
          $user = UserUtil::getVar('uname');
          //check to see that this user had not asked for more than 10 defs

          $items = ModUtil::apiFunc('Book', 'user', 'getglossary', array('user' => $user));
          if (count($items) < 11) {
          //check to see if it is already defined.
          $ispresent = ModUtil::apiFunc('Book', 'user', 'findglossaryterm', array('term' => $term));
          if (!$ispresent) {
          // The API function is called.
          $gid = ModUtil::apiFunc('Book', 'admin', 'createglossary', array('term' => $term, 'definition' => "", 'url' => $url, 'user' => $user));

          if ($gid != false) {
          // Success
          $comment = __('Thank you for submitting this word. The authors will define it soon.');
          }
          } else {
          //if the book term is defined then redirect to that term.
          //make all lowercase before making the url
          $term = strtolower($term);
          $url = ModUtil::url('book', 'admin', 'dodeletearticle') . "#$term";
          new RedirectResponse($url);
          }
          } else {
          $comment = __('You can only submit 10 words per user.');
          }
          } else {
          $comment = __('You need to be logged in to suggest words to define.');
          }
          } else {
          $comment = __('Phrases to define can be no longer that 3 words.');
          }
          }
          //make sure that a glassary term is not already defined.
          $this->view->assign('comment', $comment);
          return $this->view->fetch('book_user_glossaddcomment.tpl');
         */
    }

    /**
     * @Route("/collecthighlights")
     * 
     * Take all the highlights that a user has highlight for the book
     * and then display them to the user. This should be a useful study tool
     * @param Request $request
     * @return type
     */
    public function collecthighlightsAction(Request $request) {

//Find the current user ID
        $uid = UserUtil::getVar('uid');
        /*
          $cids = FormUtil::getPassedValue('cids', isset($args['cids']) ? $args['cids'] : null);

          $do_chaps = isset($cids);
          //Check to make sure it is valid
          if ($uid == "") {
          //user id is empty, we are not in
          return LogUtil::addWarningPopup(__('You are not logged in. In this case you cannot add highlights.'));
          }

          //get all the highligts for this user
          $highlights = ModUtil::apiFunc('Book', 'user', 'gethighlights', array('uid' => $uid));


          //collect all the centents from the articles
          //walk through each highlight and get the important information
          $highlight_text = array();
          $article_title = array();
          $article_chapter = array();
          $article_section = array();
          $aids = array();

          foreach ($highlights as $hItem) {
          //grab each article
          $article = ModUtil::apiFunc('Book', 'user', 'getarticle', array('aid' => $hItem['aid']));

          //now check for authorization, if not just continue. Frankly there should be none of these
          if (!$this->hasPermission("Book::Chapter", "$article[bid]::$article[cid]", ACCESS_READ)) {
          continue;
          }

          //if we have the chapter array, then check to see
          //if we want to grab this chapter
          $chapter = ModUtil::apiFunc('Book', 'user', 'getchapter', array('cid' => $article[cid]));
          if ($do_chaps) {
          if (!in_array($chapter['number'], $cids)) {
          continue;
          }
          }

          $article_chapter[] = $chapter['number'];
          //grab that portion of the article that is highlighted.
          $highlight_text[] = substr($article['contents'], $hItem['start'], $hItem['end'] - $hItem['start']);
          $article_title[] = $article['title'];
          $article_section[] = $article['number'];
          $aids[] = $article['aid'];
          }

          $this->view->assign('aids', $aids);
          $this->view->assign('content', $highlight_text);
          $this->view->assign('title', $article_title);
          $this->view->assign('section', $article_section);
          $this->view->assign('chapter', $article_chapter);


          return $this->view->fetch('book_user_collecthighlights.tpl');
         * 
         */
    }

    /**
     * @Route("/dohighlight/{article}/{inText}")
     *
     * The user has presumably selected some text. Change the highlight on it
     * so that it is yellow. 
     * @param Request $request
     * @param BookArticlesEntity $article
     * @param type $inText
     * @return boolean|RedirectResponse
     */
    public function dohighlightAction(Request $request, BookArticlesEntity $article, $inText) {
        return $this->redirect($this->generateUrl('paustianbookmodule_user_index'));
        /*
          //grab the user
          if ($inText == "") {
          return LogUtil::addWarningPopup(__('You must choose a selection to hilight before calling this function.'));
          }
          //Get the referring url
          $url = pnServerGetVar('HTTP_REFERER');
          if ($aid < 0) {
          return LogUtil::addWarningPopup(__('You can only highligh text in articles'));
          }


          //grab the article to find the offsets
          $art_array = ModUtil::apiFunc('Book', 'user', 'getarticle', array('aid' => $aid));
          //before doing anything else, make sure they are authorized to highlight.

          if (!$this->hasPermission('Book::Chapter', "$art_array[bid]::$art_array[cid]", ACCESS_READ)) {
          return LogUtil::registerPermissionError();
          }
          $content = $art_array['contents'];

          $uid = UserUtil::getVar('uid');
          if ($uid == "") {
          //user id is empty, we are not in
          return LogUtil::addWarningPopup(__('You are not logged in. In this case you cannot add highlights.'));
          }

          //find the offsets
          //first extract the first three words and last three words of the
          //incoming text
          //get rid of any newlines in the content and in the in text.
          $content = preg_replace('/[\n|\r]/', ' ', $content);
          $inText = preg_replace('/[\n|\r]/', ' ', $inText);
          $inText = preg_replace('|<a class="glossary".*?>|', '<a class="glossary">', $inText);
          $front_text = preg_quote(substr($inText, 0, 40));

          preg_match("|$front_text|", $content, $matches, PREG_OFFSET_CAPTURE);
          $start = $matches[0][1];
          $end = $start + strlen($inText);

          if ($end == 0 || ($start > $end)) {

          //print "Start: $start, End: $end <br />";
          return LogUtil::addWarningPopup(__('You cannot highligh that text. Try a slightly different selection.') . "start:$start end:$end");
          }


          //finally make sure that this area is not already highlighted.
          //if it is, unhighlight the area.
          $currentHighLights = ModUtil::apiFunc('book', 'user', 'gethighlights', array('uid' => $uid,
          'aid' => $aid));

          $recordHighlight = true;
          if ($currentHighLights) {
          //cycle through each highlight and see if we
          //already have it highlighted. If so, remove the highlight
          foreach ($currentHighLights as $hItem) {
          if (($start >= $hItem['start']) && ($start < $hItem['end'])) {
          $recordHighlight = false;
          //now put in a call to delete the highlight
          $success = ModUtil::apiFunc('book', 'admin', 'deletehighlight', array('udid' => $hItem['udid']));
          if (!$success) {
          return DataUtil::formatForDisplayHTML("I was unable to delete that highlight");
          } else {
          //if we deleted a highlight, then we are done.
          return new RedirectResponse($url);
          }
          }
          }
          }
          if (!$recordHighlight) {
          return new RedirectResponse($url);
          }

          //record this in the database;
          if (!ModUtil::apiFunc('Book', 'admin', 'createhighlight', array('uid' => $uid,
          'aid' => $aid,
          'start' => $start,
          'end' => $end))) {
          //set an error message and return false
          SessionUtil::setVar('error_msg', __('Highlighting failed.') . "dohighlight");
          return false;
          }
          //finally redirect to the page again, this time with highlights
          return new RedirectResponse($url);;
         * 
         */
    }

    /**
     * @Route("/download")
     * 
     * @param Request $request
     * @return type
     */
    public function downloadAction(Request $request) {
        $allow_dl = false;
        if (UserUtil::isLoggedIn()) {
            $uid = UserUtil::getVar('uid');
            $groups = UserUtil::getGroupsForUser($uid);
            //print_r($groups);die;
            //This is a real hack in that you have to know the group number
            if (array_search(3, $groups)) {
                $allow_dl = true;
            }
        }
        return $this->render('PaustianBookModule:User:download.html.twig', ['allow_dl' => $allow_dl]);
    }

}
