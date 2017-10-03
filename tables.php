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
//TODO: check to see if this is called. I doubt it. You could just delete it and see if the module still works.
function book_pntables()
{
    // Initialise table array
    $table = array();
    
    //get a name for the chapter table
    $bookName = DBUtil::getLimitedTablename('book');
   
    //set up a table with chapter names and id
    $table['name'] = $bookName;
    $table['name_column'] = array (  'bid' => $bookName . '_id',
                                        'name' => $bookName . '_name');
	
    $table['name_column_def'] = array (  'bid' => 'I(5) NOTNULL AUTO PRIMARY',
                                        		'name' => 'X'); 
    
    //get a name for the chapter table
    $bookChap = DBUtil::getLimitedTablename('book_chap');
    
    //set up a table with chapter names and id
    $table['book_chaps'] = $bookChap;
    $table['book_chaps_column'] = array (  'cid' => $bookChap . '_id',
                                        'number' => $bookChap . '_number',
                                        'bid' => $bookChap . '_bid',
                                        'name' => $bookChap . '_name');
    
    $table['book_chaps_column_def'] = array (  'cid' => 'I(5) NOTNULL AUTO PRIMARY',
                                            'number' => 'I(5) NOTNULL DEFAULT 0',
                                            'bid' => 'I(5) NOTNULL DEFAULT 0',
                                            'name' => 'X');
                                        
    // Get the name for the book item table.  This is not necessary
    // but helps in the following statements and keeps them readable
    $book = DBUtil::getLimitedTablename('book_art');

    // Set the table name
    $table['book'] = $book;

    // Set the column names.  Note that the array has been formatted
    // on-screen to be very easy to read by a user.
    $table['book_column'] = array('aid'    => $book . '_id',
                                    'title'   => $book . '_title',
                                    'cid' => $book . '_cid',
                                    'bid' => $book . '_bid',
                                    'contents' => $book . '_contents',
                                    'counter' => $book . '_counter',
                                    'lang'    => $book . '_lang',
                                    'next'    => $book . '_next',
                                    'prev'    => $book . '_prev',
                                    'aid' => $book . '_number');
    
    $table['book_column_def'] = array('aid'    => 'I(10) NOTNULL AUTO PRIMARY',
                                    'title'   => "X NOTNULL DEFAULT ''",
                                    'cid' => 'I(5) NOTNULL DEFAULT 0',
                                    'bid' => 'I(5) NOTNULL DEFAULT 0',
                                    'contents' => "XL DEFAULT ''",
                                    'counter' => 'I(11) NOTNULL DEFAULT 0',
                                    'lang'    => "C(30) NOTNULL DEFAULT 'eng'",
                                    'next'    => 'I(10) NOTNULL DEFAULT 0',
                                    'prev'    => 'I(10) NOTNULL DEFAULT 0',
                                    'aid' => 'I(10) NOTNULL DEFAULT 0');
    
    $bookFigures = DBUtil::getLimitedTablename('book_figs');
    
    $table['book_figures'] = $bookFigures;
    $table['book_figures_column'] = array (  'fid' => $bookFigures . '_fid',
    					'fig_number' => $bookFigures . '_fig_number',
                                        'chap_number' => $bookFigures . '_number',
                                        'bid' => $bookFigures . '_bid',
                                        'img_link' => $bookFigures . '_img_link',
                                        'title' => $bookFigures . '_title',
                                        'perm' => $bookFigures . '_perm',
                                        'content' => $bookFigures . '_content');
   	
    $table['book_figures_column_def'] = array (  'fid' => 'I(11) NOTNULL AUTO PRIMARY',
    										'fig_number' => 'I(11) NOTNULL',
                                        'number' => 'I(5) NOTNULL',
                                        'bid' => 'I(5) NOTNULL',
                                        'img_link' => "X NOTNULL DEFAULT ''",
                                        'title' => "X NOTNULL DEFAULT ''",
                                        'perm' => 'I(1) NOTNULL DEFAULT 1',
                                        'content' => "XL NOTNULL  DEFAULT ''");
    
 
    $bookGlossary = DBUtil::getLimitedTablename('book_gloss');
    
    $table['book_glossary'] = $bookGlossary;
    $table['book_glossary_column'] = array (  'gid' => $bookGlossary . '_gid',
    					'term' => $bookGlossary . '_term',
                                        'definition' => $bookGlossary . '_definition',
					'user' => $bookGlossary . '_user',
                                        'url' => $bookGlossary . '_url');
	
    $table['book_glossary_column_def'] = array (  'gid' => 'I(11) NOTNULL AUTO PRIMARY',
    										'term' => "X NOTNULL DEFAULT ''",
                                        'definition' => "XL NOTNULL DEFAULT ''",
										'user' => "X NOTNULL DEFAULT ''",
                                        'url' => "X NOTNULL DEFAULT ''");
    
    $bookUserData = DBUtil::getLimitedTablename('book_user_data');
	$table['book_user_data'] = $bookUserData;
	$table['book_user_data_column'] = array(	'id' => $bookUserData . '_id',
										'uid' => $bookUserData . '_uid',
										'aid' => $bookUserData . '_aid',
										'start' => $bookUserData . '_start',
										'end' => $bookUserData . '_end');
	
	$table['book_user_data_column_def'] = array(	'id' => 'I(11) NOTNULL AUTO PRIMARY',
										'uid' => 'I(11) NOTNULL',
										'aid' => 'I(10) NOTNULL',
										'start' => 'I(11) NOTNULL DEFAULT 0',
										'end' => 'I(11) NOTNULL DEFAULT 0');
    // Return the table information
    return $table;
}

