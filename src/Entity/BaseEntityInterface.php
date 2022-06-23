<?php

namespace Survos\BaseBundle\Entity;

interface BaseEntityInterface
{
    public function getUniqueIdentifiers(): array;
    public function getRP(?array $addlParams=[]): array;
}
