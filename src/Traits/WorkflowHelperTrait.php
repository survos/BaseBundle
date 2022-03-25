<?php


namespace Survos\BaseBundle\Traits;


use Doctrine\ORM\EntityManagerInterface;
use Survos\WorkflowBundle\Traits\MarkingInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Workflow\WorkflowInterface;

trait WorkflowHelperTrait
{
    use JsonResponseTrait;

    protected function _transition(Request $request, MarkingInterface $entity, $transition, WorkflowInterface $stateMachine, EntityManagerInterface $entityManager, $class, $_format = 'json'): Response
    {
//        $repo = $this->entityManager->getRepository($entity::class);
        $stateMachine->apply($entity, $transition);
        $entityManager->flush();
        return $this->jsonResponse($entity, $request, $_format);
    }

}
