<?php

/**
 * Book Module
 * 
 * @version      BookListBlock,v 1.1 2015/12/23
 * @author       Timothy Paustian
 * @link         http://www.bact.wisc.edu/ 
 * @copyright    Copyright (C) 2015 by Timothy Paustian
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

namespace Paustian\BookModule\Block;

use Zikula_View;
use BlockUtil;
use ModUtil;
use SecurityUtil;

class BookListBlock extends \Zikula_Controller_AbstractBlock {
    
    /**
     * Post initialise.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // In this block we do not want caching.
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
        SecurityUtil::registerPermissionSchema('Book:BookListBlock', 'Block title::Block ID');
    }

    /**
     * get information on block
     * 
     * @author       Timothy Paustian
     * @version      $0.1 $
     * @return       array       The block information
     */
    public function info() {
        return array('module'          => 'Book',
                     'text_type'       => $this->__('Book List'),
                     'text_type_long'  => $this->__('Block of Books Available'),
                     'allow_multiple'  => false,
                     'form_content'    => false,
                     'form_refresh'    => false,
                     'show_preview'    => true);
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
        
        // Check if the Book module is available. 
        if (!ModUtil::available('Book')) {
            return false;
        }
        
        // Call the modules API to get the items
        $books = $this->entityManager->getRepository('PaustianBookModule:BookEntity')->buildtoc();
        
        // Check for no items returned
        if (empty($books)) {
            return;
        }
        //pop the last item off the list, since we don't want to list it
        array_pop($books);
        $text = $this->render('PaustianBookModule:Block:booklist_block.html.twig', ['books' => $books])->getContent();;
        $blockinfo['content'] = $text;
        return BlockUtil::themeBlock($blockinfo);
    }
    
     /**
     * @param $view
     * @param $parameters
     * @param Response|null $response
     * @return Response
     */
    private function render($view, $parameters, Response $response = null)
    {
        if ($this->has('templating')) {
            return $this->get('templating')->renderResponse($view, $parameters, $response);
        }

        return '';
    }

}

