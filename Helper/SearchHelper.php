<?php

namespace Paustian\BookModule\Helper;
/**
 * PostNuke Application Framework
 *
 * @copyright (c) 2008, Timothy Paustian
 * @link http://www.microbiologytext.com
 * @version $Id: pnsearchapi.php 22139 2008-02-07 10:57:16Z Timothy Paustian $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package
 * @subpackage Book
 */

use ModUtil;
use Zikula\Core\RouteUrl;
use Zikula\SearchModule\AbstractSearchable;
use SecurityUtil;

class SearchHelper extends AbstractSearchable
{
    /**
     * get the UI options for search form
     *
     * @param boolean $active if the module should be checked as active
     * @param array|null $modVars module form vars as previously set
     * @return string
     */
    public function getOptions($active, $modVars = null)
    {
        if (SecurityUtil::checkPermission('Book::', '::', ACCESS_READ)) {
            return $this->getContainer()->get('templating')->renderResponse('PaustianBookModule:Search:options.html.twig', array('active' => $active))->getContent();
        }
        return '';
    }


    /**
     * Get the search results
     *
     * @param array $words array of words to search for
     * @param string $searchType AND|OR|EXACT
     * @param array|null $modVars module form vars passed though
     * @return array
     */
    function getResults(array $words, $searchType = 'AND', $modVars = null)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a')
            ->from('Paustian\BookModule\Entity\BookArticlesEntity', 'a');
        $whereExp = $this->formatWhere($qb, $words, ['a.title', 'a.contents'], $searchType);
        $qb->andWhere($whereExp);
        
        
        $query = $qb->getQuery();
        $results = $query->getResult();
        $returnArray = array();
        $sessionId = session_id();
        
        foreach ($results as $article) {
            $url = new RouteUrl('paustianbookmodule_user_displayarticle', ['article' => $article->getAid()]);
            //make sure we have permission for this object.
            if (!SecurityUtil::checkPermission('Book::', $article['bid'] . "::" . $article['cid'], ACCESS_OVERVIEW)) {
                continue;
            }
            $returnArray[] = array(
                    'title' => $article->getTitle(),
                    'text' => $this->shorten_text($article->getContents()),
                    'module' => $this->name,
                    'created' => '',
                    'sesid' => $sessionId,
                    'url' => $url
                );
        }
        return $returnArray;
    }

    /**
     * private function to shorten the contents text string
     * I think the search display stuff should be doing this
     * but it is not
     */
    private function shorten_text($text) {
// Change to the number of characters you want to display
        $chars = 500;
        $text = $text . " ";
        $text = substr($text, 0, $chars);
        $text = substr($text, 0, strrpos($text, ' '));
        $text = $text . "...";

        return $text;
    }

}

