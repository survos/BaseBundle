<?php

/*
<<<<<<< HEAD
 * Based on AdminLTE bundle.
 *
=======
 * This file is part of the AdminLTE bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
>>>>>>> dev-no-adminlte-bundle
 */

namespace Survos\BaseBundle\Event;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Collect all MenuItemInterface objects that should be rendered in the menu/navigation section.
 */
class KnpMenuEvent extends Event
{
    protected ItemInterface $menu;
    protected FactoryInterface $factory;
    private array $options;
    private array $childOptions;

    /**
     * @param array $options
     * @param array $childOptions
     */
    public function __construct(ItemInterface $menu, FactoryInterface $factory, $options = [], $childOptions = [])
    {
        $this->menu = $menu;
        $this->factory = $factory;
        $this->options = $options;
        $this->childOptions = $childOptions;
    }

    public function getMenu(): ItemInterface
    {
        return $this->menu;
    }

    public function getFactory(): FactoryInterface
    {
        return $this->factory;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getChildOptions(): array
    {
        return $this->childOptions;
    }
}
