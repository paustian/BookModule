<?php

// book.php,v 1.1 2006/12/23 22:59:01 paustian Exp
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
 * @version      book.php,v 1.1 2006/12/23 22:59:01 paustian Exp
 * @author       Timothy Paustian
 * @link         http://www.bact.wisc.edu/  The PostNuke Home Page
 * @copyright    Copyright (C) 2005 by Timothy Paustian
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
class Book_Block_BookList extends Zikula_Controller_AbstractBlock {

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
        return array('module'          => $this->name,
                     'text_type'       => $this->__('Book List'),
                     'text_type_long'  => $this->__('Block of Books Available'),
                     'allow_multiple'  => true,
                     'form_content'    => false,
                     'form_refresh'    => false,
                     'show_preview'    => true,
                     'admin_tableless' => true);
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
        if (!SecurityUtil::checkPermission('Bookblock::', "$blockinfo[bid]::", ACCESS_READ)) {
            return LogUtil::registerPermissionError();
        }

        // Get variables from content block
        $vars = BlockUtil::varsFromContent($blockinfo['bid']);


        // Check if the Book module is available. 
        if (!ModUtil::available('Book')) {
            return false;
        }


        // Call the modules API to get the items
        $items = ModUtil::apiFunc('Book', 'user', 'getall');

        // Check for no items returned
        if (empty($items)) {
            return;
        }

        // Call the modules API to get the numitems
        $countitems = ModUtil::apiFunc('Book', 'user', 'countitems');

        // Create output object
        // Note that for a block the corresponding module must be passed.
        $books = array();
        // Display each item, permissions permitting
        foreach ($items as $item) {
            $item['toc'] = ModUtil::func('Book', 'user', 'toc', array('bid' => $item['bid']));
            $books[]=$item;
        }
        
        $this->view->assign('books', $books);

        // Populate block info and pass to theme
        $text = $this->view->fetch('book_block_first.htm');
        $blockinfo['content'] = $text;
        return themesideblock($blockinfo);
    }

    /**
     * modify block settings
     * 
     * @author       Timothy Paustian
     * @version      $ 0.1 $
     * @param        array       $blockinfo     a blockinfo structure
     * @return       output      the bock form
     */
    function Book_firstblock_modify($blockinfo) {
        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);


        // Create output object
        $render = Zikula_View::getInstance('Book');

        // As Admin output changes often, we do not want caching.
        //$render->caching = false;
        // assign the approriate values
        //$render->assign('numitems', $vars['numitems']);
        // Return the output that has been generated by this function
        return $render->fetch('book_block_first.htm');
    }

    /**
     * update block settings
     * 
     * @author       Timothy Paustian
     * @version      $: 0.1 $
     * @param        array       $blockinfo     a blockinfo structure
     * @return       $blockinfo  the modified blockinfo structure
     */
    function Book_firstblock_update($blockinfo) {
        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // alter the corresponding variable
        $vars['numitems'] = FormUtil::getPassedValue('numitems');

        // write back the new contents
        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        // clear the block cache
        $render = Zikula_View::getInstance('Book');
        $render->clear_cache('book_block_first.htm');

        return $blockinfo;
    }

}

?>