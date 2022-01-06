<?php

namespace Survos\BaseBundle\Menu;

use Umbrella\CoreBundle\Menu\Builder\MenuItemBuilder;

interface AdminMenuInterface
{
    public function addMenuItem(MenuItemBuilder $menu, $options): MenuItemBuilder;
}
