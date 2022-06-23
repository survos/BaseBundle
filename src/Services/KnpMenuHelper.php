<?php // Simplies the construction of menu items,
namespace Survos\BaseBundle\Services;

use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

// maybe AuthMenuHelper?  Use the OAuth stuff, too?  Or make it SurvosMenuSubscriber?
class KnpMenuHelper
{
//    public function authMenuUsingClass(AuthorizationCheckerInterface $security, ItemInterface $menu, $childOptions)
//    {
//        if ($security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
//            $menu->addChild(
//                'logout',
//                ['route' => 'app_logout', 'label' => 'menu.logout', 'childOptions' => $childOptions]
//            )->setLabelAttribute('icon', 'fas fa-sign-out-alt');
//        } else {
//            $menu->addChild(
//                'login',
//                ['route' => 'app_login', 'label' => 'menu.login', 'childOptions' => $childOptions]
//            )->setLabelAttribute('icon', 'fas fa-sign-in-alt');
//            try {
//                $menu->addChild(
//                    'register',
//                    ['route' => 'app_register', 'label' => 'menu.register', 'childOptions' => $childOptions]
//                )->setLabelAttribute('icon', 'fas fa-sign-in-alt');
//            } catch (\Exception $e) {
//                // it's possible that it's a readonly site, with an admin login, so no registration
//                // bin/console make:registration
//            }
//        }
//
//    }

}
