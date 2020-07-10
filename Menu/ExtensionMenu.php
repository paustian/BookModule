<?php

declare(strict_types=1);

/**
 * Book Module
 *
 * @version      BookListBlock,v 1.1 2015/12/23
 * @author       Timothy Paustian
 * @link         http://www.bact.wisc.edu/
 * @copyright    Copyright (C) 2015 by Timothy Paustian
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
namespace Paustian\BookModule\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Translation\Bundle\EditInPlace\Activator as EditInPlaceActivator;
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

        $menu->addChild('Book', [
            'uri' => '#',
        ])->setAttribute('icon', 'fas fa-book')
          ->setAttribute('dropdown', true);

        $menu['Book']->addChild('Create New Book', [
            'route' => 'paustianbookmodule_admin_edit',
        ])->setAttribute('icon', 'fas fa-plus');

        $menu['Book']->addChild('Edit or Delete Book', [
            'route' => 'paustianbookmodule_admin_modify',
        ])->setAttribute('icon', 'fas fa-pencil');

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'PaustianBookModule';
    }
}