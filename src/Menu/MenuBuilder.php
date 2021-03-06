<?php

/*
 * This file is part of the AdminLTE bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Survos\BaseBundle\Menu;

use Knp\Menu\ItemInterface;
use Survos\BaseBundle\Event\KnpMenuEvent;
use Knp\Menu\FactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MenuBuilder
{
    const PAGE_MENU_EVENT='page_menu';
    const SIDEBAR_MENU_EVENT='sidebar_menu';

    public function __construct(private FactoryInterface $factory, private EventDispatcherInterface $eventDispatcher)
    {
    }

    public function createSidebarMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root', [
            'attributes' => [
//                'class' => 'nav nav-sidebar flex-column',
            ],
            'childrenAttributes' => [
//                'class' => 'nav-child-attributes',
//                'data-widget' => 'treeview',
//                'data-accordion' => false,
//                'role' => 'menu'
            ],
        ]);

        $childOptions = [
//            'attributes' => ['class' => 'show treeview'],
//            'childrenAttributes' => ['class' => 'list-unstyled nav-treeview show menu-open branch'],
            'labelAttributes' => ['safe_html'=>true, 'data-bs-toggle' => 'collapse'],
        ];

        $this->eventDispatcher->dispatch(new KnpMenuEvent($menu, $this->factory, $options, $childOptions),
            self::SIDEBAR_MENU_EVENT);

        return $menu;
    }

    public function createPageMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root', [
            'class' => 'float-right',
            'childrenAttributes' => [
                'class' => 'nav nav-pills',
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
                ['class' => 'show'],
            'childrenAttributes' => ['class' => 'list-unstyled '],
            'labelAttributes' => ['safe_html'=>true, 'data-toggle' => 'xxcollapse'],
        ];

        $this->eventDispatcher->dispatch(new KnpMenuEvent($menu, $this->factory, $options, $childOptions), self::PAGE_MENU_EVENT );

        return $menu;
    }

}
