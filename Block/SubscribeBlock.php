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

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Api\CurrentUserApi;

class SubscribeBlock extends AbstractBlockHandler {

    private $currentUserApi;

    public function __construct(
        AbstractExtension $extension,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        VariableApiInterface $variableApi,
        PermissionApiInterface $permissionApi,
        Environment $twig,
        CurrentUserApi $currentUserApi
    ) {
        parent::__construct($extension, $requestStack, $translator, $variableApi, $permissionApi, $twig);
        $this->currentUserApi = $currentUserApi;
    }
    /**
     * display block
     * 
     * @author       Timothy Paustian
     * @version      1.1
     * @param        array       $blockinfo     a blockinfo structure
     * @return       output      the rendered bock
     */
    public function display(array $properties) : string {

        if (!$this->currentUserApi->isLoggedIn()) {
            $content = $this->trans('You must <a href="register">register</a> before you can purchase any books.');
        } else {
            $uid = $this->currentUserApi->get('uid');
            $content = $this->renderView('@PaustianBookModule/Block/subscribe_block.html.twig', ['uid' => $uid]);
        }
        return $content;
    }

}
