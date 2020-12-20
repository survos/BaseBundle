<?php

namespace Survos\BaseBundle\Entity;

abstract class SurvosBaseEntity
{

    // hack for https://github.com/symfony/symfony/issues/35574
    public function __sleep() { return []; }

    public function getRP(?Array $addlParams=[]): array
    {
        return array_merge($this->getUniqueIdentifiers(), $addlParams);
    }

    public function __toString()
    {
        return join('-', array_values($this->getUniqueIdentifiers()));
    }

    public function populateFromOptions(array $options ): self
    {
//        unset($options['_token']);
//        unset($options['_next_route']);
        foreach ($options as $var=>$val) {
            // isn't there a property accessor method?
            if (method_exists($this, $setter = 'set' . $var)) {
                $this->{$setter}($val);
            }
        }
        return $this;
    }

    public function getRoutePrefix()
    {
        // this or self?
        $shortName = strtolower( (new \ReflectionClass($this))->getShortName() );
        return $shortName;
    }

    public function getNextRouteChoices(): array
    {
        $routes = [];
        foreach (['edit', 'show', 'index'] as $routeType ) {
            // @todo: security
            $routes[$this->getRoutePrefix() . '_' .  $routeType] = $this->getRoutePrefix() . $routeType; // @todo add translation
        }
        return $routes;
        // by default, list and show
    }

    // default, so this works with the default for ParamConverter
    function getUniqueIdentifiers()
    {
        return [$this->getRoutePrefix() . 'Id' => $this->getId()];
    }

}
