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
        // Get datbase setup - note that both pnDBGetConn() and pnDBGetTables()
        // return arrays but we handle them differently.  For pnDBGetConn()
        // we currently just want the first item, which is the official
        // database handle.  For pnDBGetTables() we want to keep the entire
        // tables array together for easy reference later on
        // Create table
        try {
            DoctrineHelper::createSchema($this->entityManager, array('Book_Entity_Book'));
        } catch (Exception $e) {
            return false;
        }
        try {
            DoctrineHelper::createSchema($this->entityManager, array('Book_Entity_BookArticles'));
        } catch (Exception $e) {
            return false;
        }
        try {
            DoctrineHelper::createSchema($this->entityManager, array('Book_Entity_BookChapters'));
        } catch (Exception $e) {
            return false;
        }
        try {
            DoctrineHelper::createSchema($this->entityManager, array('Book_Entity_BookFigures'));
        } catch (Exception $e) {
            return false;
        }
        try {
            DoctrineHelper::createSchema($this->entityManager, array('Book_Entity_BookGloss'));
        } catch (Exception $e) {
            return false;
        }
        try {
            DoctrineHelper::createSchema($this->entityManager, array('Book_Entity_BookUserData'));
        } catch (Exception $e) {
            return false;
        }
        // These are used in the searching functions.
        ModUtil::setVar('Book', 'SEARCH_BOOK_LABEL', __('Search Books'));
        ModUtil::setVar('Book', 'BOOKS_LABEL', __('Books'));
        ModUtil::setVar('Book', 'securebooks', false);
        
        HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());
        // Initialisation successful
        return true;
    }

    /**
     * upgrade the book module from an old version
     * This function can be called multiple times
     */
    public function upgrade($oldversion) {
        // Upgrade dependent on old version number
        switch ($oldversion) {
            case 1.0:
                //We can leave this as mysql specific code because you could not use anything but mysql before 2.0
                $dbconn = & pnDBGetConn(true);
                $pntable = & pnDBGetTables();

                $bookFigures = $pntable['book_figures'];
                $bookFigList = &$pntable['book_figures_column'];

                // Code to upgrade from version 1.0 goes here
                //add the permission field to the figure table
                $sql = "ALTER TABLE $bookFigures ADD $bookFigList[perm] TINYINT DEFAULT 1 NOT NULL";
                $dbconn->Execute($sql);

                // Check for an error with the database code, and if so set an
                // appropriate error message and return
                if ($dbconn->ErrorNo() != 0) {
                    return false;
                }

                $bookGlossary = $pntable['book_glossary'];
                $bookGlssaryList = &$pntable['book_glossary_column'];
                $sql = "ALTER TABLE $bookGlossary ADD $bookGlssaryList[user] TEXT DEFAULT '', ADD $bookGlssaryList[url] TEXT DEFAULT ''";

                $dbconn->Execute($sql);

                // Check for an error with the database code, and if so set an
                // appropriate error message and return
                if ($dbconn->ErrorNo() != 0) {
                    return false;
                }
                //make it possible to prevent simultaneous use by more than one person.
                pnModSetVar('Book', 'securebooks', false);
                break;
            case 2.0:
                // No code is needed to upgrade to 2.0 from 1.0
                break;
            case 2.1:
                //we need to add code that changes the table names and gets rid of book_
                //in front of table names and book_fig and book_gloss and book_user_data
                $connection = Doctrine_Manager::getInstance()->getConnection('default');
                $sqlStatements = array();
                //Change the Book table
                $sqlStatements[] = "ALTER TABLE  `book` CHANGE  `book_id`  `bid` BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
                    CHANGE  `book_name`  `name` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";
                
                //Change the articles table
                $sqlStatements[] = "ALTER TABLE  `book_art` CHANGE  `book_art_id`  `aid` BIGINT( 20 ) NOT NULL AUTO_INCREMENT ,
                    CHANGE  `book_art_title`  `title` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
                    CHANGE  `book_art_chap_id`  `cid` BIGINT( 20 ) NOT NULL DEFAULT  '0',
                    CHANGE  `book_art_book_id`  `bid` BIGINT( 20 ) NOT NULL DEFAULT  '0',
                    CHANGE  `book_art_contents`  `contents` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
                    CHANGE  `book_art_counter`  `counter` BIGINT( 20 ) NOT NULL DEFAULT  '0',
                    CHANGE  `book_art_lang`  `lang` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'eng',
                    CHANGE  `book_art_next`  `next` BIGINT( 20 ) NOT NULL DEFAULT  '0',
                    CHANGE  `book_art_prev`  `prev` BIGINT( 20 ) NOT NULL DEFAULT  '0',
                    CHANGE  `book_art_number`  `number` BIGINT( 20 ) NOT NULL DEFAULT  '0'";
                //Change the chapters table
                $sqlStatements[] = "ALTER TABLE  `book_chap` CHANGE  `book_chap_id`  `cid` BIGINT( 20 ) NOT NULL AUTO_INCREMENT ,
                    CHANGE  `book_chap_number`  `number` BIGINT( 20 ) NOT NULL DEFAULT  '0',
                    CHANGE  `book_chap_book_id`  `bid` BIGINT( 20 ) NOT NULL DEFAULT  '0',
                    CHANGE  `book_chap_name`  `name` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";
                //Change the Figures table
                $sqlStatements[] = "ALTER TABLE  `book_figs` CHANGE  `book_figs_fig_id`  `fid` BIGINT( 20 ) NOT NULL AUTO_INCREMENT ,
                    CHANGE  `book_figs_fig_number`  `fig_number` BIGINT( 20 ) NOT NULL ,
                    CHANGE  `book_figs_chap_number`  `chap_number` BIGINT( 20 ) NOT NULL ,
                    CHANGE  `book_figs_book_id`  `bid` BIGINT( 20 ) NOT NULL ,
                    CHANGE  `book_figs_img_link`  `img_link` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
                    CHANGE  `book_figs_fig_title`  `title` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
                    CHANGE  `book_figs_fig_perm`  `perm` TINYINT( 4 ) NOT NULL DEFAULT  '1',
                    CHANGE  `book_figs_content`  `content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
                //Change the Glossary Table
                $sqlStatements[] = "ALTER TABLE  `book_gloss` CHANGE  `book_gloss_gloss_id`  `gid` BIGINT( 20 ) NOT NULL AUTO_INCREMENT ,
                    CHANGE  `book_gloss_term`  `term` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
                    CHANGE  `book_gloss_definition`  `definition` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
                    CHANGE  `book_gloss_user`  `user` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
                    CHANGE  `book_gloss_url`  `url` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
                //Finally changet the user data table
                $sqlStatements[] = "ALTER TABLE  `book_user_data` CHANGE  `book_user_data_id`  `udid` BIGINT( 20 ) NOT NULL AUTO_INCREMENT ,
                    CHANGE  `book_user_data_uid`  `uid` BIGINT( 20 ) NOT NULL ,
                    CHANGE  `book_user_data_art_id`  `aid` BIGINT( 20 ) NOT NULL ,
                    CHANGE  `book_user_data_start`  `start` BIGINT( 20 ) NOT NULL DEFAULT  '0',
                    CHANGE  `book_user_data_end`  `end` BIGINT( 20 ) NOT NULL DEFAULT  '0'";
                
                foreach ($sqlStatements as $sql) {
                    $stmt = $connection->prepare($sql);
                    try {
                        $stmt->execute();
                    } catch (Exception $e) {
                        // trap and toss exceptions if you need to.
                    }
                }
                
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
        DoctrineHelper::dropSchema($this->entityManager, array('Book_Entity_Book'));
        DoctrineHelper::dropSchema($this->entityManager, array('Book_Entity_BookArticles'));
        DoctrineHelper::dropSchema($this->entityManager, array('Book_Entity_BookChapters'));
        DoctrineHelper::dropSchema($this->entityManager, array('Book_Entity_BookFigures'));
        DoctrineHelper::dropSchema($this->entityManager, array('Book_Entity_BookGloss'));
        DoctrineHelper::dropSchema($this->entityManager, array('Book_Entity_BookUserData'));

        // Delete any module variables
        ModUtil::delVar('Book', 'securebooks', false);

        // Deletion successful
        return true;
    }

}

?>