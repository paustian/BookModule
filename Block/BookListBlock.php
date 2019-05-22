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

use Zikula\BlocksModule\AbstractBlockHandler;

class BookListBlock extends AbstractBlockHandler{
    
    /**
     * display block
     * 
     * @author       Timothy Paustian
     * @version      1.1
     * @param        array       $properties
     * @return       output      the rendered bock
     */
    public function display(array $properties) {
        
        $em = $this->get('doctrine')->getManager();
        // Call the modules API to get the items
        $books = $em->getRepository('PaustianBookModule:BookEntity')->buildtoc();
        
        // Check for no items returned
        if (empty($books)) {
            return;
        }
        //pop the last item off the list, since we don't want to list it
        array_pop($books);
        $text = $this->renderView('PaustianBookModule:Block:booklist_block.html.twig', ['books' => $books]);
        return $text;
    }
    
     public function getFormClassName() {
        return null;
    }

}

