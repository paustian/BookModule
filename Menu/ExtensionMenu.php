<?php

declare(strict_types=1);

/**
 * Book Module
 *
 * @version      5.0.0
 * @author       Timothy Paustian
 * @link         http://www.bact.wisc.edu/
 * @copyright    Copyright (C) 2020 by Timothy Paustian
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
namespace Paustian\BookModule\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class ExtensionMenu implements ExtensionMenuInterface
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;


    public function __construct(
        FactoryInterface $factory,
        PermissionApiInterface $permissionApi
    ) {
        $this->factory = $factory;
        $this->permissionApi = $permissionApi;
    }

    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        if (self::TYPE_ADMIN === $type) {
            return $this->getAdmin();
        }

        return null;
    }

    private function getAdmin(): ?ItemInterface
    {
        if (!$this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            return null;
        }

        $menu = $this->factory->createItem('bookAdminMenu');

        //Book functions
        $menu->addChild('Book', [
            'uri' => '#',
        ])->setAttribute('icon', 'fas fa-book')
          ->setAttribute('dropdown', true);

        $menu['Book']->addChild('Create New Book', [
            'route' => 'paustianbookmodule_admin_edit',
        ])->setAttribute('icon', 'fas fa-plus');

        $menu['Book']->addChild('Edit, Export, or Delete Book', [
            'route' => 'paustianbookmodule_admin_modify',
        ])->setAttribute('icon', 'fas fa-pencil');

        //Chapter Menu
        $menu->addChild(
            'Chapter', ['uri' => '#',
        ])->setAttribute('icon', 'fas fa-bookmark')
        ->setAttribute('dropdown', true);
        $menu['Chapter']->addChild('Create New Chapter', [
            'route' => 'paustianbookmodule_admin_editchapter',
        ])->setAttribute('icon', 'fas fa-plus');
        $menu['Chapter']->addChild('Edit, Delete, Export, Search/Replace, or check URLS in Chapter', [
            'route' => 'paustianbookmodule_admin_modifychapter',
        ])->setAttribute('icon', 'fas fa-edit');
        $menu['Chapter']->addChild('Import Chapter', [
            'route' => 'paustianbookmodule_admin_import',
        ])->setAttribute('icon', 'fas fa-upload');

        //Article
        $menu->addChild(
            'Article', ['uri' => '#',
        ])->setAttribute('icon', 'fas fa-newspaper')
            ->setAttribute('dropdown', true);
        $menu['Article']->addChild('Create New Article', [
            'route' => 'paustianbookmodule_admin_createarticle',
        ])->setAttribute('icon', 'fas fa-plus');
        $menu['Article']->addChild('Edit or Delete Article', [
            'route' => 'paustianbookmodule_admin_modifyarticle',
        ])->setAttribute('icon', 'fas fa-edit');
        $menu['Article']->addChild('Arrange Articles', [
            'route' => 'paustianbookmodule_admin_arrangearticles',
        ])->setAttribute('icon', 'fas fa-sort');

        //Figure
        $menu->addChild(
            'Figure', ['uri' => '#',
        ])->setAttribute('icon', 'fas fa-image')
            ->setAttribute('dropdown', true);
        $menu['Figure']->addChild('Create New Figure', [
            'route' => 'paustianbookmodule_admin_createfigure',
        ])->setAttribute('icon', 'fas fa-plus');
        $menu['Figure']->addChild('Edit or Delete Figure', [
            'route' => 'paustianbookmodule_admin_modifyfigure',
        ])->setAttribute('icon', 'fas fa-edit');

        //Glossary
        $menu->addChild(
            'Glossary', ['uri' => '#',
        ])->setAttribute('icon', 'fas fa-books')
            ->setAttribute('dropdown', true);
        $menu['Glossary']->addChild('Create New Glossary', [
            'route' => 'paustianbookmodule_admin_createglossary',
        ])->setAttribute('icon', 'fas fa-plus');
        $menu['Glossary']->addChild('Edit or Delete Glossary', [
            'route' => 'paustianbookmodule_admin_modifyglossary',
        ])->setAttribute('icon', 'fas fa-edit');
        $menu['Glossary']->addChild('Import Glossary', [
            'route' => 'paustianbookmodule_admin_importglossary',
        ])->setAttribute('icon', 'fas fa-upload');
        $menu['Glossary']->addChild('Check for Student Definitions', [
            'route' => 'paustianbookmodule_admin_checkstudentdefs',
        ])->setAttribute('icon', 'fas fa-user-graduate');

        $menu->addChild('Settings', [
            'route' => 'paustianbookmodule_config_config',
        ])->setAttribute('icon', 'fas fa-wrench');

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'PaustianBookModule';
    }
}