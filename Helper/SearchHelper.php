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


use Paustian\BookModule\Entity\Repository\BookArticlesRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\SearchModule\Entity\SearchResultEntity;
use Zikula\SearchModule\SearchableInterface;
use Zikula\Core\RouteUrl;

class SearchHelper implements SearchableInterface
{
    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * $var BookArticlesRepository
     */
    private $articleRepo;

    /**
     * SearchHelper constructor.
     * @param PermissionApiInterface $permissionApi
     * @param SessionInterface $session
     * @param BookArticlesRepository $articleRepo
     */
    public function __construct(
        PermissionApiInterface $permissionApi,
        SessionInterface $session,
        BookArticlesRepository $articleRepo)
    {
        $this->permissionApi = $permissionApi;
        $this->session = $session;
        $this->articleRepo = $articleRepo;
    }
    /**
     * {@inheritdoc}
     */
    public function amendForm(FormBuilderInterface $form)
    {
        // not needed because `active` child object is already added and that is all that is needed.
    }

    /**
     * {@inheritdoc}
     */
    public function getResults(array $words, $searchType = 'AND', $modVars = null)
    {
        $returnArray = [];

        $hits= $this->articleRepo->getSearchResults($words, $searchType);
        $sessionID = $this->session->getId();
        foreach ($hits as $article) {
            $url = new RouteUrl('paustianbookmodule_user_displayarticle', ['article' => $article->getAid()]);
            //make sure we have permission for this object.
            if ($this->permissionApi->hasPermission('Book::', $article->getBid() . "::" . $article->getCid(), ACCESS_READ)) {
                $result = new SearchResultEntity();
                $result->setTitle($article->getTitle())
                    ->setModule('PaustianBookModule')
                    ->setText($this->shorten_text($article->getContents(), $words))
                    ->setSesid($sessionID)
                    ->setUrl($url);
                $returnArray[] = $result;
            } else {
                $result = new SearchResultEntity();
                $result->setTitle($article->getTitle())
                ->setModule('PaustianBookModule')
                 ->setText($this->shorten_text($article->getContents(), $words))
                 ->setSesid($sessionID);
                $returnArray[] = $result;
            }
        }
        return $returnArray;
    }

    public function getErrors()
    {
        return [];
    }

    /**
     * private function to shorten the contents text string
     * I think the search display stuff should be doing this
     * but it is not
     */
    private function shorten_text($text, $words) {
// Change to the number of characters you want to display
        $chars = 500;
        $startPos = 0;
        $text = strip_tags($text);
        //Find the start position of the first match
        foreach($words as $search_string) {
            if (preg_match("|$search_string|", $text, $matches, PREG_OFFSET_CAPTURE) === 1) {
                $startPos = $matches[0][1];
                if ($startPos > 100) {
                    $startPos = $startPos - 100;
                }
            }
        }

        $text = substr($text, $startPos, $chars);
        $text .=  "...";

        return $text;
    }

}

