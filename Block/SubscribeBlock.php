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

use Symfony\Component\HttpFoundation\Request;
use Zikula_View;
use SecurityUtil;
use BlockUtil;
use ModUtil;
use UserUtil;
use System;

//HOLD OFF ON THIS UNTIL 1.4.2 IS OUT
class SubscribeBlock extends \Zikula_Controller_AbstractBlock {

    /**
     * Post-construction initialization.
     *
     * @return void
     */
    protected function postInitialize() {
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
        SecurityUtil::registerPermissionSchema('SubscribeBlock:', 'Block title::');
    }

    /**
     * get information on block
     * 
     * @author       Timothy Paustian
     * @version      $0.1 $
     * @return       array       The block information
     */
    public function info() {
        return array('text_type' => 'Subscription Block',
            'module' => 'Book',
            'text_type_long' => 'A simple block for displaying a paypal subscribe buttons with the user id as a custom parameter',
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
        if (!UserUtil::isLoggedIn()) {
            $blockinfo['content'] = __('You must <a href="register">register</a> before you can purchase any books.');
        } else {
            $uid = $this->serviceManager->get('session')->get('uid');
            $blockinfo['content'] = $this->render('PaustianBookModule:Block:subscribe_block.html.twig', ['uid' => $uid])->getContent();
        }
        return BlockUtil::themeBlock($blockinfo);
    }

// Original PHP code by Chirp Internet: www.chirp.com.au
// Please acknowledge use of this code by including this header.

    function myTruncate2($string, $limit, $break = " ", $pad = "...") {
// return with no change if string is shorter than $limit
        if (strlen($string) <= $limit)
            return $string;

        $string = substr($string, 0, $limit);
        if (false !== ($breakpoint = strrpos($string, $break))) {
            $string = substr($string, 0, $breakpoint);
        }

        return $string . $pad;
    }

    /**
     * @param $view
     * @param $parameters
     * @param Response|null $response
     * @return Response
     */
    private function render($view, $parameters, Response $response = null) {
        if ($this->has('templating')) {
            return $this->get('templating')->renderResponse($view, $parameters, $response);
        }

        return '';
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
