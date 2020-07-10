<?php

declare(strict_types=1);

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

use Zikula\BlocksModule\AbstractBlockHandler;

class SubscribeBlock extends AbstractBlockHandler {

    /**
     * display block
     * 
     * @author       Timothy Paustian
     * @version      1.1
     * @param        array       $blockinfo     a blockinfo structure
     * @return       output      the rendered bock
     */
    public function display(array $properties) : string {
        $currentUserApi = $this->get('zikula_users_module.current_user');
        if (!$currentUserApi->isLoggedIn()) {
            $content = $this->trans('You must <a href="register">register</a> before you can purchase any books.');
        } else {
            $uid = $em = $this->get('session')->get('uid');
            $content = $this->renderView('@PaustianBookModule/Block/subscribe_block.html.twig', ['uid' => $uid]);
        }
        return $content;
    }

}
