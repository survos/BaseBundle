<?php
namespace Survos\BaseBundle;

use Survos\BaseBundle\DependencyInjection\Compiler\SurvosBaseCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SurvosBaseBundle extends Bundle
{
    public function getPath(): string
    {
        return __DIR__;
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SurvosBaseCompilerPass());
    }

    // for new best practices directory structure https://github.com/symfony/symfony/blob/5.4/UPGRADE-5.0.md#httpkernel
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

}
