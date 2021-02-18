<?php

/*
 * Based on AdminLTE bundle.
 *
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

    /**
     * @return FactoryInterface
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return array
     */
    public function getChildOptions()
    {
        return $this->childOptions;
    }
}
