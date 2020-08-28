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

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Api\CurrentUserApi;

class ToolsBlock extends AbstractBlockHandler {

    private $em;

    private $currentUserApi;

    public function __construct(
        AbstractExtension $extension,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        VariableApiInterface $variableApi,
        PermissionApiInterface $permissionApi,
        Environment $twig,
        CurrentUserApi $currentUserApi,
        EntityManagerInterface $entityManagerInterface
    ) {
        parent::__construct($extension, $requestStack, $translator, $variableApi, $permissionApi, $twig);
        $this->currentUserApi = $currentUserApi;
        $this->em = $entityManagerInterface;
    }

    /**
     * display block
     * 
     * @author       Timothy Paustian
     * @version      1.1
     * @param        array       $blockinfo     a blockinfo structure
     * @return       output      the rendered bock
     */
    public function display(array $properties) :string {
        // ToDo: Not sure how to get repository. It looks like you inject it, but not sure how.
        if (!$this->currentUserApi->isLoggedIn()) {
            return '';
        }
        
        $url = $_SERVER['REQUEST_URI'];
        $content = "";
        //first try to get the book id
        //the book tools are only useful when an article is being displayed.
        $pattern = '|displayarticle/([0-9]{1,3})|';
        $matches = array();
        if (preg_match($pattern, $url, $matches)) {
            $aid = $matches[1];
            $article = $this->em->getRepository('PaustianBookModule:BookArticlesEntity')->find($aid);
            $repo = $this->em->getRepository('PaustianBookModule:BookEntity');
            $booktoc = $repo->buildtoc($article->getBid());
            $content = $this->renderView('@PaustianBookModule/Block/tools_block.html.twig', ['aid' => $aid, 'book' => $booktoc[0]]);
        }
        return $content;
    }
}