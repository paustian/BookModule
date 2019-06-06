<?php

namespace Paustian\BookModule;


use Zikula\Core\AbstractExtensionInstaller;
use Paustian\BookModule\Entity\BookArticlesEntity;
use Paustian\BookModule\Entity\BookChaptersEntity;
use Paustian\BookModule\Entity\BookEntity;
use Paustian\BookModule\Entity\BookFiguresEntity;
use Paustian\BookModule\Entity\BookGlossEntity;
use Paustian\BookModule\Entity\BookUserDataEntity;

class BookModuleInstaller extends AbstractExtensionInstaller {
    private $entities = [
            BookArticlesEntity::class,
            BookChaptersEntity::class,
            BookEntity::class,
            BookFiguresEntity::class,
            BookGlossEntity::class,
            BookUserDataEntity::class,
    ];
    
    /**
     * initialise the book module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     */
    public function install() {
        //Create the tables of the module. Book has 5
        try {
            $this->schemaTool->create($this->entities);
        } catch (\Exception $e) {
            return false;
        }
        $this->setVar('securebooks', false);

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
                    } catch (\Exception $e) {
                        // trap and toss exceptions if you need to.
                        echo($e);
                        die;
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
        try {
            $this->schemaTool->drop($this->entities);
        } catch (\PDOException $e) {
            return false;
        }

        // Delete any module variables.
        $this->delVars();

        // Deletion successful*/
        return true;
    }

}

