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
use UserUtil;


class ToolsBlock extends AbstractBlockHandler {
    /**
     * display block
     * 
     * @author       Timothy Paustian
     * @version      1.1
     * @param        array       $blockinfo     a blockinfo structure
     * @return       output      the rendered bock
     */
    public function display(array $properties) :string {
        $currentUserApi = $this->get('zikula_users_module.current_user');
        if (!$currentUserApi->isLoggedIn()) {
            return '';
        }
        
        $url = $_SERVER['REQUEST_URI'];
        $content = "";
        //first try to get the book id
        //the book tools are only useful when an article is being displayed.
        $pattern = '|displayarticle/([0-9]{1,3})|';
        $em = $this->get('doctrine')->getManager();
        $matches = array();
        if (preg_match($pattern, $url, $matches)) {
            $aid = $matches[1];
            $article = $em->getRepository('PaustianBookModule:BookArticlesEntity')->find($aid);
            $repo = $em->getRepository('PaustianBookModule:BookEntity');
            $booktoc = $repo->buildtoc($article->getBid(), $chapterids);
            $content = $this->renderView('@PaustianBookModule/Block/tools_block.html.twig', ['aid' => $aid, 'book' => $booktoc[0]]);
        }
        return $content;
    }
}