<?php

namespace Survos\BaseBundle\Entity;

trait RouteParametersTrait
{
    public function getUniqueIdentifiers(): array
    {
        return [strtolower( (new \ReflectionClass($this))->getShortName() ) . 'Id' => $this->getId()];
    }

    public function getRP(?array $addlParams=[]): array
    {
        return array_merge($this->getUniqueIdentifiers(), $addlParams);
    }
}
