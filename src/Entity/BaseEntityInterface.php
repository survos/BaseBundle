<?php

namespace Survos\BaseBundle\Entity;

interface BaseEntityInterface
{
    function getUniqueIdentifiers(): array;
    public function getRP(?array $addlParams=[]): array;
}
