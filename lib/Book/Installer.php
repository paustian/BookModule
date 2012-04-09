<?php

// pninit.php,v 1.9 2007/02/03 20:32:56 paustian Exp
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
// Original Author of file: Jim McDonald
// Purpose of file:  Initialisation functions for book
// ----------------------------------------------------------------------

class Book_Installer extends Zikula_AbstractInstaller {

    /**
     * initialise the book module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     */
    public function install() {
        // Get datbase setup - note that both pnDBGetConn() and DBUtil::getTables()
        // return arrays but we handle them differently.  For pnDBGetConn()
        // we currently just want the first item, which is the official
        // database handle.  For DBUtil::getTables() we want to keep the entire
        // tables array together for easy reference later on
// Create table
        if (!DBUtil::createTable('book_name')) {
            return false;
        }
        if (!DBUtil::createTable('book_chaps')) {
            return false;
        }

        if (!DBUtil::createTable('book')) {
            return false;
        }

        if (!DBUtil::createTable('book_figures')) {
            return false;
        }

        if (!DBUtil::createTable('book_glossary')) {
            return false;
        }

        if (!DBUtil::createTable('book_user_data')) {
            return false;
        }
        // These are used in the searching functions.
        ModUtil::setVar('Book', 'SEARCH_BOOK_LABEL', __('Search Books'));
        ModUtil::setVar('Book', 'BOOKS_LABEL', __('Books'));
        ModUtil::setVar('Book', 'securebooks', false);

        // Initialisation successful
        return true;
    }

    /**
     * upgrade the book module from an old version
     * This function can be called multiple times
     * I don't know if this will work, but I also don't think anyone is using 
     * the old modules. I will fix it if anyone ever needs it.
     */
    public function upgrade($oldversion) {
        // Upgrade dependent on old version number
        switch ($oldversion) {
            case 0.1:
                if (!DBUtil::createTable('book_user_data')) {
                    return false;
                }

            case 1.0:
                //We can leave this as mysql specific code because you could not use anything but mysql before 2.0
                $dbconn = & Doctrine_Manager::getInstance()->getCurrentConnection();
                $pntable = & DBUtil::getTables();

                $bookFigures = $pntable['book_figures'];
                $bookFigList = &$pntable['book_figures_column'];

                // Code to upgrade from version 1.0 goes here
                //add the permission field to the figure table
                $sql = "ALTER TABLE $bookFigures ADD $bookFigList[fig_perm] TINYINT DEFAULT 1 NOT NULL";
                $dbconn->Execute($sql);

                // Check for an error with the database code, and if so set an
                // appropriate error message and return
                if ($dbconn->ErrorNo() != 0) {
                    return false;
                }

                $bookGlossary = $pntable['book_glossary'];
                $bookGlssaryList = &$pntable['book_glossary_column'];
                $sql = "ALTER TABLE $bookGlossary ADD $bookGlssaryList[user] TEXT DEFAULT '', ADD $bookGlssaryList[URL] TEXT DEFAULT ''";

                $dbconn->Execute($sql);

                // Check for an error with the database code, and if so set an
                // appropriate error message and return
                if ($dbconn->ErrorNo() != 0) {
                    return false;
                }
                //make it possible to prevent simultaneous use by more than one person.
                ModUtil::setVar('Book', 'securebooks', false);
                
            case 2.0:
                // No code is needed to upgrade to 2.0 from 1.0
            case 2.1:
                break;
        }

        // Update successful
        return true;
    }

    /**
     * delete the book module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     */
    public function uninstall() {
        //drop the tables
        if (!DBUtil::dropTable('book_name')) {
            return false;
        }
        if (!DBUtil::dropTable('book_chaps')) {
            return false;
        }

        if (!DBUtil::dropTable('book')) {
            return false;
        }

        if (!DBUtil::dropTable('book_figures')) {
            return false;
        }

        if (!DBUtil::dropTable('book_glossary')) {
            return false;
        }

        if (!DBUtil::dropTable('book_user_data')) {
            return false;
        }

        // Delete any module variables
        pnModDelVar('Book', 'securebooks', false);

        // Deletion successful
        return true;
    }

}

?>