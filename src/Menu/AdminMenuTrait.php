<?php

namespace Survos\BaseBundle\Menu;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Umbrella\CoreBundle\Menu\Builder\MenuItemBuilder;
use function Symfony\Component\String\u;

trait AdminMenuTrait
{
    public function addMenuItem(MenuItemBuilder $menu, $options): MenuItemBuilder
    {
        $options = $this->menuOptions($options);

        $item = $menu->add($options['id'])
            ->label($options['label'])
            ;

        if ($options['icon']) {
            $item->icon($options['icon']);
        }

        if ($options['badge']) {
            $item->badge($options['badge']);
        }

        if ($options['route']) {
            $item
                ->route($options['route'], $options['routeParameters'] ?? []);
        }

        $item
            ->end();
        return $item;

    }

    private function menuOptions(array $options, array $extra = []): array
    {
        // idea: make the label a . version of the route, e.g. project_show could be project.show
        // we could also set a default icon for things like edit, show
        $options = (new OptionsResolver())
            ->setDefaults([
                // deprecated, use 'id' instead
                'menu_code' => null,
                'extras' => [],
                'id' => null,
                'route' => null,
                'rp' => null,
                'external' => false,
                '_fragment' => null,
                'label' => null,
                'icon' => null,
                'badge' => null,
                'feather' => null,
                'uri' => null,
                'classes' => [], // this doesn't feel quite right.  Maybe a "style: header"?
                'style' => null,
//                'childOptions' => $this->childOptions?? null,
                'description' => null,
                'attributes' => []
            ])->resolve($options);

        // rename rp
        if (is_object($options['rp'])) {
            $options['routeParameters'] = $options['rp']->getRp();
            if (empty($options['icon'])) {
                $iconConstant = get_class($options['rp']) . '::ICON';
                $options['icon'] = defined($iconConstant) ? constant($iconConstant) : 'fas fa-database'; // generic database entity
            }
        } elseif (is_array($options['rp'])) {
            $options['routeParameters'] = $options['rp'];
        }
        // if (isset($options['rp'])) { dd($options);}
        unset($options['rp']);
        if (empty($options['label']) && ($routeLabel = $options['route'])) {
            // _index is commonly used to list entities
            $routeLabel = preg_replace('/_index$/', '', $routeLabel);
            $routeLabel = preg_replace('/^app_/', '', $routeLabel);
            $options['label'] = u($routeLabel)->replace('_', ' ')->title(true)->toString();
        }

        if (empty($options['label']) && $options['menu_code']) {
            $options['label'] = u($options['menu_code'])->replace('.', ' ')->title(true)->replace('_header', '')->toString();
        }

        // if label is exactly true then automate the label from the route
        if ($options['label'] === true) {
            $options['label'] = str_replace('_', '.', $options['route']);
        }

        // we could pass in a hash route and hash params instead.
        if ($fragment = $options['_fragment']) {
            $options['uri'] = '#' . $fragment;
            unset($options['route']);
            //
        }

        // default icons, should be configurable in survos_base.yaml
        if ($route = $options['route']) {
            if ($options['icon'] === null) {
                foreach ([
                             'show' => 'fas fa-eye',
                             'edit' => 'fas fa-wrench'
                         ] as $regex => $icon) {
                    if (preg_match("|$regex|", $route)) {
                        $options['data-icon'] = $icon;
                    }
                }
            }
        }



        // move the icon to attributes, where it belongs IF this is knp_menu
        if ($options['icon']) {
            $options['attributes']['data-icon'] = $options['icon'];
//            $options['attributes']['class'] = 'text-danger';
            $options['label_attributes']['data-icon'] = $options['icon'];
//            unset($options['icon']); // ??
        }

        if ($options['style'] === 'header') {
            $options['attributes']['class'] = 'sidebar-header';
        }

        if (!$options['id']) {
            $options['id'] = $options['menu_code'];
        }

        if (empty($options['id'])) {
            $options['id'] = md5(json_encode($options));
        }
        return $options;
    }


}
