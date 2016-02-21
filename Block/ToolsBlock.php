<?php

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
 * @version      tools.php,v 1.2 2016/02/21
 * @author       Timothy Paustian
 * @link         http://www.bact.wisc.edu/  The PostNuke Home Page
 * @copyright    Copyright (C) 2016 by Timothy Paustian
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
namespace Paustian\BookModule\Block;

use Zikula_View;
use SecurityUtil;
use BlockUtil;
use ModUtil;

class ToolsBlock extends \Zikula_Controller_AbstractBlock {
    
    /**
     * Post-construction initialization.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // Disable caching by default.
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
    }
    /**
     * initialise block
     * 
     * @author       Timothy Paustian
     * @version      $0.1 $
     */
    public function init() {
        // Security
        SecurityUtil::registerPermissionSchema('ToolsBlock:', 'Block title::');
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
        
        $url = System::getCurrentUrl();
        //first try to get the book id
        $pattern = '';
        $pattern = '|bid/([0-9]{1,3})|';
        $matches = array();
        preg_match($pattern, $url, $matches);
        $aid = -1;
        $bid = -1;
        if ($matches[1] == "") {
            //next try aid
            $pattern = '|aid/([0-9]{1,3})|';
            preg_match($pattern, $url, $matches);
            if ($matches[1] == "") {
                //OK now try the cid
                $pattern = '|cid/([0-9]{1,3})|';
                preg_match($pattern, $url, $matches);
                if ($matches[1] == "") {
                    //if we get here, we must not be in a book, so just return
                    return false;
                }
                //$chapter = ModUtil::func('PauistanBookModule', 'user', 'getchapter', array('cid' => $matches[1]));
                //$bid = $chapter['bid'];
            } else {
                /* aid was found */
               // $article = ModUtil::apiFunc('Book', 'user', 'getarticle', array('aid' => $matches[1]));
                //$bid = $article['bid'];
            }
        } else {
            //$bid = $matches[1];
        }

        //$content = ModUtil::apiFunc('Book', 'user', 'shorttoc', array('bid' => $bid,
         //           'aid' => $aid));

        //$blockinfo['content'] = $content;
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