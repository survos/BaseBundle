<?php

/*
 * This file is based on the MenuBuilder for KnpMenu in Kevin Papst's AdminLTE bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with that source code.
 */

namespace Survos\BaseBundle\Menu;

use Knp\Menu\ItemInterface;
use Knp\Menu\FactoryInterface;
use Survos\BaseBundle\Event\KnpMenuEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MenuBuilder
{
    public function __construct(private FactoryInterface $factory, private EventDispatcherInterface $eventDispatcher)
    {
    }

    public function createAppMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('menuroot');
        $this->eventDispatcher->dispatch(new KnpMenuEvent($menu, $this->factory, $options));
        return $menu;
    }

    public function createNavbarMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('menuroot', [
//            'currentClass' => 'text-danger current-class active show',

        ]);
        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        $this->eventDispatcher->dispatch(new KnpMenuEvent($menu, $this->factory, $options), KnpMenuEvent::NAVBAR_MENU_EVENT);
        return $menu;
    }

    public function createSidebarMenu(array $options): ItemInterface
    {
        assert(count($options), "Missing get options");
        $menu = $this->factory->createItem('menuroot',
            [
                'label' => "Menu Root",
                'first' => 'FIRSTCLASS',
                'currentClass' => 'text-danger current-class active show',
                'ancestorClass' => 'text-warning ancestor-class active show',

                'attributes' => [
                    'class' => 'nav nav-sidebar flex-column menuroot-attributes-class',
                ],
// @todo: pass these, so they an depend on what theme is being used.
                'listAttributes' => [
                    'class' => 'nav nav-treeviewXX listAttributes-class'
                ],
                'childrenAttributes' => [
                    'class' => 'nav-link nav-treeview',
                    'data-widget' => 'treeview',
                    'data-accordion' => 'false',
                    'role' => 'menu'
                ],
            ]);

        $childOptions = [
            'attributes' => ['class' => 'nav-treeview'],
            'childrenAttributes' => ['class' => 'list-unstyled nav-treeview show menu-open branch'],
            'labelAttributes' => ['safe_html' => true, 'data-bs-toggle' => 'collapse'],
        ];

        $this->eventDispatcher->dispatch(new KnpMenuEvent($menu, $this->factory, $options, $childOptions),
            KnpMenuEvent::SIDEBAR_MENU_EVENT);

        return $menu;
    }

    public function createPageMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root', [
            'class' => 'float-right',
            'childrenAttributes' => [
                'class' => 'nav nav-pills childrenAttributes-class pageMenuClass',
                // 'data-widget' => 'nav',
                'data-accordion' => false,
                // 'role' => 'menu'
            ],
        ]);

        /* this doesnt seem to work well.
        $menu
            ->addChild('title', ['label' => $options['title'], 'uri' => '#'])
            ->setAttribute('class', 'h6 float-right');
        */

        $childOptions = [
            'attributes' =>
                ['class' => 'show childOptions-class'],
            'childrenAttributes' => ['class' => 'list-unstyled '],
            'labelAttributes' => ['safe_html' => true, 'data-toggle' => 'xxcollapse'],
        ];

        $this->eventDispatcher->dispatch(new KnpMenuEvent($menu, $this->factory, $options, $childOptions), KnpMenuEvent::PAGE_MENU_EVENT);

        return $menu;
    }

}
