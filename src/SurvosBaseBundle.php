<?php
namespace Survos\BaseBundle;

use Survos\BaseBundle\DependencyInjection\Compiler\SurvosBaseCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SurvosBaseBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SurvosBaseCompilerPass());
    }

}
