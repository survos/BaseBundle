<?php


namespace Survos\BaseBundle\Traits;


trait QueryBuilderHelperTrait
{
    public function getCounts($field): array
    {
        $results = $this->createQueryBuilder('s')
            ->groupBy('s.' . $field)
            ->select(["s.$field, count(s) as count"])
            ->getQuery()
            ->getArrayResult();
        $counts = [];
        foreach ($results as $r) {
            $counts[$r[$field]] = $r['count'];
        }

        return $counts;
    }

}
