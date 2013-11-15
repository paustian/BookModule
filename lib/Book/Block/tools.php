<?php

// tools.php,v 1.1 2006/12/23 22:59:01 paustian Exp
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
/**
 * Book Module
 * 
 * The Book module shows how to make a PostNuke module. 
 * It can be copied over to get a basic file structure.
 *
 * Purpose of file:  administration display functions -- 
 *                   This file contains all administrative GUI functions 
 *                   for the module
 *
 * @package      PostNuke_Miscellaneous_Modules
 * @subpackage   Book
 * @version      tools.php,v 1.1 2006/12/23 22:59:01 paustian Exp
 * @author       Timothy Paustian
 * @link         http://www.bact.wisc.edu/  The PostNuke Home Page
 * @copyright    Copyright (C) 2005 by Timothy Paustian
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
class Book_Block_Tools extends Zikula_Controller_AbstractBlock {

    /**
     * initialise block
     * 
     * @author       Timothy Paustian
     * @version      $0.1 $
     */
    public function init() {
        // Security
        SecurityUtil::registerPermissionSchema('Bookblock:', 'Block title::');
    }

    /**
     * get information on block
     * 
     * @author       Timothy Paustian
     * @version      $0.1 $
     * @return       array       The block information
     */
    public function info() {
        return array('text_type' => 'module',
            'module' => 'Book',
            'text_type_long' => 'Tools for use in Books',
            'allow_multiple' => true,
            'form_content' => true,
            'form_refresh' => true,
            'show_preview' => true);
    }

    /**
     * display block
     * 
     * @author       Timothy Paustian
     * @version      1.1
     * @param        array       $blockinfo     a blockinfo structure
     * @return       output      the rendered bock
     */
    public function display($blockinfo) {
        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing.  
        // Note that we have Book:Firstblock: as the component.
        if (!SecurityUtil::checkPermission('Bookblock::', "$blockinfo[bid]::", ACCESS_OVERVIEW) || !pnUserLoggedIn()) {
            return false;
        }
        // Get variables from content block
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // Check if the Book module is available. 
        if (!ModUtil::available('Book')) {
            return false;
        }
        $short_urls = System::getVar('shorturls');
        $url = System::getCurrentUrl();
        //first try to get the book id
        $pattern = '';
        if ($short_urls) {
            $pattern = '|bid/([0-9]{1,3})|';
        } else {
            $pattern = '|bid=([0-9]{1,3})|';
        }
        $matches = array();
        preg_match($pattern, $url, $matches);
        $aid = -1;
        $bid = -1;
        if ($matches[1] == "") {
            //next try aid
            if ($short_urls) {
                $pattern = '|aid/([0-9]{1,3})|';
            } else {
                $pattern = '|aid=([0-9]{1,3})|';
            }
            preg_match($pattern, $url, $matches);
            if ($matches[1] == "") {
                //OK now try the cid
                if ($short_urls) {
                    $pattern = '|cid/([0-9]{1,3})|';
                } else {
                    $pattern = '|cid=([0-9]{1,3})|';
                }
                preg_match($pattern, $url, $matches);
                if ($matches[1] == "") {
                    //if we get here, we must not be in a book, so just return
                    return false;
                }
                $chapter = ModUtil::apiFunc('Book', 'user', 'getchapter', array('cid' => $matches[1]));
                $bid = $chapter['bid'];
            } else {
                /* aid was found */
                $article = ModUtil::apiFunc('Book', 'user', 'getarticle', array('aid' => $matches[1]));
                $bid = $article['bid'];
            }
        } else {
            $bid = $matches[1];
        }

        $content = ModUtil::func('Book', 'user', 'shorttoc', array('bid' => $bid,
                    'aid' => $aid));

        $blockinfo['content'] = $content;
        return BlockUtil::themeBlock($blockinfo);
    }

    /**
     * modify block settings
     * 
     * @author       Timothy Paustian
     * @version      $ 0.1 $
     * @param        array       $blockinfo     a blockinfo structure
     * @return       output      the bock form
     */
    public function modify($blockinfo) {
        return $blockinfo['content'];
    }

    /**
     * update block settings
     * 
     * @author       Timothy Paustian
     * @version      $: 0.1 $
     * @param        array       $blockinfo     a blockinfo structure
     * @return       $blockinfo  the modified blockinfo structure
     */
    public function update($blockinfo) {
        return $blockinfo;
    }

}

?>