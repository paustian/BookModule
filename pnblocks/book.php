<?php
// book.php,v 1.6 2006/12/24 03:38:04 paustian Exp
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
 * @version      book.php,v 1.6 2006/12/24 03:38:04 paustian Exp
 * @author       Timothy Paustian
 * @link         http://www.bact.wisc.edu/  The PostNuke Home Page
 * @copyright    Copyright (C) 2005 by Timothy Paustian
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */ 


/**
 * initialise block
 * 
 * @author       Timothy Paustian
 * @version      $0.1 $
 */
function Book_bookblock_init()
{
    // Security
    pnSecAddSchema('Bookblock:', 'Block title::');
}

/**
 * get information on block
 * 
 * @author       Timothy Paustian
 * @version      $0.1 $
 * @return       array       The block information
 */
function Book_bookblock_info()
{
    return array('text_type'      => 'First',
                 'module'         => 'Book',
                 'text_type_long' => 'Display a list of books',
                 'allow_multiple' => true,
                 'form_content'   => false,
                 'form_refresh'   => false,
                 'show_preview'   => true);
}

/**
 * display block
 * 
 * @author       Timothy Paustian
 * @version      1.6
 * @param        array       $blockinfo     a blockinfo structure
 * @return       output      the rendered bock
 */
function Book_bookblock_display($blockinfo)
{
    // Security check - important to do this as early as possible to avoid
    // potential security holes or just too much wasted processing.  
	// Note that we have Book:Firstblock: as the component.
    if (!SecurityUtil::checkPermission('Bookblock::',
                         "$blockinfo[title]::",
                         ACCESS_READ)) {
        return false;
    }

    // Get variables from content block
    $vars = pnBlockVarsFromContent($blockinfo['content']);


	// Check if the Book module is available. 
	if (!pnModAvailable('Book')) {
		return false;
	}

	
    // Call the modules API to get the items
    $items = pnModAPIFunc('Book', 
                          'user',  
                          'getall');	

	// Check for no items returned
	if (empty($items)) {
	    return;
	}

    // Call the modules API to get the numitems
    $countitems = pnModAPIFunc('Book', 
                          'user',  
                          'countitems');	   
		
    // Create output object
	// Note that for a block the corresponding module must be passed.
	$pnRender =& new pnRender('Book');
	
    // Display each item, permissions permitting
	$shown_results = 0;
	$bookitems = array();
	foreach ($items as $item) {

        if (SecurityUtil::checkPermission('Book::Chapter', "$item[book_id]::.*", ACCESS_OVERVIEW)) {
	        	$bookitems[] = array('url'   => pnModURL('Book', 'user', 'toc', array('book_id' => $item['book_id'])),
			    	                    'title' => $item['book_name'],
			    	                    'book_id' => $item['book_id']);
        	} else {
		    	$bookitems[] = array('title' => $item['title']);
        	}
    }
    //A flag to detect if we should show the book ids
    if (SecurityUtil::checkPermission('Book::', '::', ACCESS_ADMIN)) {
		$pnRender->assign('show_internals', true);
	}
    $pnRender->assign('book_names', $bookitems);
	
    // Populate block info and pass to theme
    $blockinfo['content'] = $pnRender->fetch('book_block_first.htm');

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
function Book_firstblock_modify($blockinfo)
{
    // Get current content
    $vars = pnBlockVarsFromContent($blockinfo['content']);

  
    // Create output object
	$pnRender =& new pnRender('Book');

	// As Admin output changes often, we do not want caching.
	//$pnRender->caching = false;

    // assign the approriate values
	//$pnRender->assign('numitems', $vars['numitems']);

    // Return the output that has been generated by this function
	return $pnRender->fetch('book_block_first.htm');
}


/**
 * update block settings
 * 
 * @author       Timothy Paustian
 * @version      $: 0.1 $
 * @param        array       $blockinfo     a blockinfo structure
 * @return       $blockinfo  the modified blockinfo structure
 */
function Book_firstblock_update($blockinfo)
{
    // Get current content
    $vars = pnBlockVarsFromContent($blockinfo['content']);
	
	// alter the corresponding variable
    $vars['numitems'] = FormUtil::getPassedValue('numitems');
	
	// write back the new contents
    $blockinfo['content'] = pnBlockVarsToContent($vars);

	// clear the block cache
	$pnRender =& new pnRender('Book');
	$pnRender->clear_cache('book_block_first.htm');
	
    return $blockinfo;
}

?>