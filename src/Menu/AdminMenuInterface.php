<?php

namespace Survos\BaseBundle\Menu;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Umbrella\CoreBundle\Menu\Builder\MenuItemBuilder;

interface AdminMenuInterface
{
    public function addMenuItem(MenuItemBuilder $menu, $options): MenuItemBuilder;
    public function menuOptions(array $options, array $extra = []): array; // hack
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker): void;
    public function isGranted($attribute, $subject=null): bool;

}
