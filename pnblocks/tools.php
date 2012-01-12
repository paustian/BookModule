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


/**
 * initialise block
 * 
 * @author       Timothy Paustian
 * @version      $0.1 $
 */
function Book_toolsblock_init()
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
function Book_toolsblock_info()
{
    return array('text_type'      => 'module',
                 'module'         => 'Book',
                 'text_type_long' => 'Tools for use in Books',
                 'allow_multiple' => true,
                 'form_content'   => true,
                 'form_refresh'   => true,
                 'show_preview'   => true);
}

/**
 * display block
 * 
 * @author       Timothy Paustian
 * @version      1.1
 * @param        array       $blockinfo     a blockinfo structure
 * @return       output      the rendered bock
 */
function Book_toolsblock_display($blockinfo)
{
    // Security check - important to do this as early as possible to avoid
    // potential security holes or just too much wasted processing.  
	// Note that we have Book:Firstblock: as the component.
    if (!SecurityUtil::checkPermission('Bookblock::',
                         "$blockinfo[book_id]::",
                         ACCESS_OVERVIEW) || !pnUserLoggedIn()) {
        return false;
    }
   // Get variables from content block
    $vars = pnBlockVarsFromContent($blockinfo['content']);

	// Check if the Book module is available. 
	if (!pnModAvailable('Book')) {
		return false;
	}

	$url = pnGetCurrentURL();
	//first try to get the book id
	$pattern = '/book_id=([0-9]{1,3})/';
	$matches = array();
	preg_match($pattern, $url, $matches);
	$art_id = -1;
	$book_id = -1;
	if($matches[1]==""){
		//next try art_id
		$pattern = '/art_id=([0-9]{1,3})/';
		preg_match($pattern, $url, $matches);
		if($matches[1]==""){
			//OK now try the chap_id
			$pattern = '/chap_id=([0-9]{1,3})/';
			preg_match($pattern, $url, $matches);
			if($matches[1]==""){
				//if we get here, we must not be in a book, so just return
				return false;
			}
			$chapter = pnModAPIFunc('Book', 'user', 'getchapter', array('chap_id' => $matches[1]));
			$book_id = $chapter['book_id'];
		} else {
			/*art_id was found*/
			$article = pnModAPIFunc('Book', 'user', 'getarticle', array('art_id' => $matches[1]));
			$book_id = $article['book_id'];
		}
	} else {
		$book_id = $matches[1];
	}
	//print $book_id . "<br />";
	//print_r($matches); die;
	
	$content = pnModFunc('Book', 'user', 'shorttoc', 
						array('book_id'=> $book_id, 
							'art_id' => $art_id));

	$blockinfo['content'] = $content;
    return pnBlockThemeBlock($blockinfo);
}


/**
 * modify block settings
 * 
 * @author       Timothy Paustian
 * @version      $ 0.1 $
 * @param        array       $blockinfo     a blockinfo structure
 * @return       output      the bock form
 */
function Book_toolsblock_modify($blockinfo)
{
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
function Book_toolsblock_update($blockinfo)
{
    return $blockinfo;
}

?>