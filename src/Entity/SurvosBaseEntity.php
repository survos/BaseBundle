<?php

namespace Survos\BaseBundle\Entity;

abstract class SurvosBaseEntity
{
    abstract function getUniqueIdentifiers();

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

    public function populateFromOptions(array $options ): array
    {
        unset($options['_token']);
        unset($options['_next_route']);
        foreach ($options as $var=>$val) {
            // isn't there a property accessor method?
            $this->{'set' . $var}($val);
        }
        return $options;
    }

    public function getRoutePrefix()
    {
        // this or self?
        $shortName = strtolower( (new \ReflectionClass($this))->getShortName() );
        return $shortName . '_';
    }

    public function getNextRouteChoices(): array
    {
        $routes = [];
        foreach (['edit', 'show', 'index'] as $routeType ) {
            // @todo: security
            $routes[$this->getRoutePrefix() . $routeType] = $this->getRoutePrefix() . $routeType; // @todo add translation
        }
        return $routes;
        // by default, list and show

    }

}
