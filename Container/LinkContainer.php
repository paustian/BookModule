<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Paustian\BookModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class LinkContainer implements LinkContainerInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * constructor.
     *
     * @param TranslatorInterface $translator
     * @param RouterInterface $router
     * @param PermissionApiInterface $permissionApi
     **/
    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        PermissionApiInterface $permissionApi
    )
    {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
    }

    /**
     * get Links of any type for this extension
     * required by the interface
     *
     * @param string $type
     * @return array
     */
    public function getLinks($type = LinkContainerInterface::TYPE_ADMIN)
    {
        if (LinkContainerInterface::TYPE_ADMIN == $type) {
            return $this->getAdmin();
        }
        if (LinkContainerInterface::TYPE_ACCOUNT == $type) {
            return $this->getAccount();
        }
        if (LinkContainerInterface::TYPE_USER == $type) {
            return $this->getUser();
        }

        return [];
    }

    /**
     * get the Admin links for this extension
     *
     * @return array
     */
    private function getAdmin()
    {
        $links = [];
        
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {


            $submenulinks = [];
            $submenulinks[] = [
                'url' => $this->router->generate('paustianbookmodule_admin_edit'),
                'text' => $this->translator->__('Create New Book'),
                ];

            $submenulinks[] = [
                'url' => $this->router->generate('paustianbookmodule_admin_modify'),
                'text' => $this->translator->__('Edit or Delete Book'),
                 ];

            $links[] = [
                'url' => $this->router->generate('paustianbookmodule_admin_edit'),
                'text' => $this->translator->__('Books'),
                'icon' => 'book',
                'links' => $submenulinks];

            $submenulinks3 = [];
            $submenulinks3[] = [
                'url' => $this->router->generate('paustianbookmodule_admin_editchapter'),
                'text' => $this->translator->__('Create New Chapter'),];
            $submenulinks3[] = [
                'url' => $this->router->generate('paustianbookmodule_admin_modifychapter'),
                'text' => $this->translator->__('Edit, Delete, Export, Search/Replace, or check URLS in Chapter'),];
            $submenulinks3[] = [
                'url' => $this->router->generate('paustianbookmodule_admin_import'),
                'text' => $this->translator->__('Import Chapter'),];
            $links[] = [
                'url' => $this->router->generate('paustianbookmodule_admin_editchapter'),
                'text' => $this->translator->__('Chapters'),
                'icon' => 'bookmark',
                'links' => $submenulinks3];

            $submenulinks4 = [];
            $submenulinks4[] =[
                'url' => $this->router->generate('paustianbookmodule_admin_editarticle'),
                'text' => $this->translator->__('Create New Article')];
            $submenulinks4[] = [
                'url' => $this->router->generate('paustianbookmodule_admin_modifyarticle'),
                'text' => $this->translator->__('Edit or Delete Article')];
            $submenulinks4[] = [
                'url' => $this->router->generate('paustianbookmodule_admin_arrangearticles'),
                'text' => $this->translator->__('Arrange Articles')];
            $links[] = [
                'url' => $this->router->generate('paustianbookmodule_admin_editarticle'),
                'text' => $this->translator->__('Articles'),
                'icon' => 'list',
                'links' => $submenulinks4];

            $submenulinks5 = [];
            $submenulinks5[] = [
                'url' => $this->router->generate('paustianbookmodule_admin_editfigure'),
                'text' => $this->translator->__('Create New Figure')];
            $submenulinks5[] = [
                'url' => $this->router->generate('paustianbookmodule_admin_modifyfigure'),
                'text' => $this->translator->__('Edit or Delete Figure')];
            $links[] = [
                'url' => $this->router->generate('paustianbookmodule_admin_editfigure'),
                'text' => $this->translator->__('Figures'),
                'icon' => 'line-chart',
                'links' => $submenulinks5];


            $submenulinks2 = [];
            $submenulinks2[] = [
                'url' => $this->router->generate('paustianbookmodule_admin_editglossary'),
                'text' => $this->translator->__('Create New Glossary Item')];
            $submenulinks2[] = [
                'url' => $this->router->generate('paustianbookmodule_admin_modifyglossary'),
                'text' => $this->translator->__('Edit or Delete Glossary Item')];
            $submenulinks2[] = [
                'url' => $this->router->generate('paustianbookmodule_admin_importglossary'),
                'text' => $this->translator->__('Import Glossary')];
            $submenulinks2[] = [
                'url' => $this->router->generate('paustianbookmodule_admin_checkstudentdefs'),
                'text' => $this->translator->__('Check for Requested Definitions')];
            $links[] = [
                'url' => $this->router->generate('paustianbookmodule_admin_editglossary'),
                'text' => $this->translator->__('Glossary'),
                'icon' => 'book',
                'links' => $submenulinks2];

        }
        return $links;
    }

    private function getUser()
    {
        $links = [];

        return $links;
    }

    private function getAccount()
    {
        $links = [];

        return $links;
    }

    /**
     * set the BundleName as required by the interface
     *
     * @return string
     */
    public function getBundleName()
    {
        return 'PaustianBookModule';
    }
}
