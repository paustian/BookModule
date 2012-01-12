<?php
// pntables.php,v 1.4 2007/01/13 20:45:35 paustian Exp
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
// Purpose of file:  Table information for book module
// ----------------------------------------------------------------------

/**
 * This function is called internally by the core whenever the module is
 * loaded.  It adds in the information
 */
function book_pntables()
{
    // Initialise table array
    $pntable = array();
    
    //get a name for the chapter table
    $bookName = DBUtil::getLimitedTablename('book');
   
    //set up a table with chapter names and id
    $pntable['book_name'] = $bookName;
    $pntable['book_name_column'] = array (  'book_id' => $bookName . '_id',
                                        'book_name' => $bookName . '_name');
	
    $pntable['book_name_column_def'] = array (  'book_id' => 'I(5) NOTNULL AUTO PRIMARY',
                                        		'book_name' => 'X'); 
    
    //get a name for the chapter table
    $bookChap = DBUtil::getLimitedTablename('book_chap');
    
    //set up a table with chapter names and id
    $pntable['book_chaps'] = $bookChap;
    $pntable['book_chaps_column'] = array (  'chap_id' => $bookChap . '_id',
                                        'chap_number' => $bookChap . '_number',
                                        'book_id' => $bookChap . '_book_id',
                                        'chap_name' => $bookChap . '_name');
    
    $pntable['book_chaps_column_def'] = array (  'chap_id' => 'I(5) NOTNULL AUTO PRIMARY',
                                            'chap_number' => 'I(5) NOTNULL DEFAULT 0',
                                            'book_id' => 'I(5) NOTNULL DEFAULT 0',
                                            'chap_name' => 'X');
                                        
    // Get the name for the book item table.  This is not necessary
    // but helps in the following statements and keeps them readable
    $book = DBUtil::getLimitedTablename('book_art');

    // Set the table name
    $pntable['book'] = $book;

    // Set the column names.  Note that the array has been formatted
    // on-screen to be very easy to read by a user.
    $pntable['book_column'] = array('art_id'    => $book . '_id',
                                    'title'   => $book . '_title',
                                    'chap_id' => $book . '_chap_id',
                                    'book_id' => $book . '_book_id',
                                    'contents' => $book . '_contents',
                                    'counter' => $book . '_counter',
                                    'lang'    => $book . '_lang',
                                    'next'    => $book . '_next',
                                    'prev'    => $book . '_prev',
                                    'art_number' => $book . '_number');
    
    $pntable['book_column_def'] = array('art_id'    => 'I(10) NOTNULL AUTO PRIMARY',
                                    'title'   => "X NOTNULL DEFAULT ''",
                                    'chap_id' => 'I(5) NOTNULL DEFAULT 0',
                                    'book_id' => 'I(5) NOTNULL DEFAULT 0',
                                    'contents' => "XL DEFAULT ''",
                                    'counter' => 'I(11) NOTNULL DEFAULT 0',
                                    'lang'    => "C(30) NOTNULL DEFAULT 'eng'",
                                    'next'    => 'I(10) NOTNULL DEFAULT 0',
                                    'prev'    => 'I(10) NOTNULL DEFAULT 0',
                                    'art_number' => 'I(10) NOTNULL DEFAULT 0');
    
    $bookFigures = DBUtil::getLimitedTablename('book_figs');
    
    $pntable['book_figures'] = $bookFigures;
    $pntable['book_figures_column'] = array (  'fig_id' => $bookFigures . '_fig_id',
    										'fig_number' => $bookFigures . '_fig_number',
                                        'chap_number' => $bookFigures . '_chap_number',
                                        'book_id' => $bookFigures . '_book_id',
                                        'img_link' => $bookFigures . '_img_link',
                                        'fig_title' => $bookFigures . '_fig_title',
                                        'fig_perm' => $bookFigures . '_fig_perm',
                                        'fig_content' => $bookFigures . '_content');
   	
    $pntable['book_figures_column_def'] = array (  'fig_id' => 'I(11) NOTNULL AUTO PRIMARY',
    										'fig_number' => 'I(11) NOTNULL',
                                        'chap_number' => 'I(5) NOTNULL',
                                        'book_id' => 'I(5) NOTNULL',
                                        'img_link' => "X NOTNULL DEFAULT ''",
                                        'fig_title' => "X NOTNULL DEFAULT ''",
                                        'fig_perm' => 'I(1) NOTNULL DEFAULT 1',
                                        'fig_content' => "XL NOTNULL  DEFAULT ''");
    
 
    $bookGlossary = DBUtil::getLimitedTablename('book_gloss');
    
    $pntable['book_glossary'] = $bookGlossary;
    $pntable['book_glossary_column'] = array (  'gloss_id' => $bookGlossary . '_gloss_id',
    					'term' => $bookGlossary . '_term',
                                        'definition' => $bookGlossary . '_definition',
					'user' => $bookGlossary . '_user',
                                        'URL' => $bookGlossary . '_URL');
	
    $pntable['book_glossary_column_def'] = array (  'gloss_id' => 'I(11) NOTNULL AUTO PRIMARY',
    										'term' => "X NOTNULL DEFAULT ''",
                                        'definition' => "XL NOTNULL DEFAULT ''",
										'user' => "X NOTNULL DEFAULT ''",
                                        'URL' => "X NOTNULL DEFAULT ''");
    
    $bookUserData = DBUtil::getLimitedTablename('book_user_data');
	$pntable['book_user_data'] = $bookUserData;
	$pntable['book_user_data_column'] = array(	'id' => $bookUserData . '_id',
										'uid' => $bookUserData . '_uid',
										'art_id' => $bookUserData . '_art_id',
										'start' => $bookUserData . '_start',
										'end' => $bookUserData . '_end');
	
	$pntable['book_user_data_column_def'] = array(	'id' => 'I(11) NOTNULL AUTO PRIMARY',
										'uid' => 'I(11) NOTNULL',
										'art_id' => 'I(10) NOTNULL',
										'start' => 'I(11) NOTNULL DEFAULT 0',
										'end' => 'I(11) NOTNULL DEFAULT 0');
    // Return the table information
    return $pntable;
}

?>