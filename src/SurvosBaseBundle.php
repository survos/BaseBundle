<?php
namespace Survos\BaseBundle;

use Survos\BaseBundle\DependencyInjection\Compiler\SurvosBaseCompilerPass;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SurvosBaseBundle extends AbstractBundle
{
//    public function getPath(): string {
//        return __DIR__;
//    }
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->register('survos_base_bundle.base_service', BaseService::class)
            ->setPublic(true)
            ->setAutowired(true);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
                 ->children()
                 ->scalarNode('theme')
                 ->info('the theme code for rendering templates')
                 ->end()
                 ->arrayNode('routes')
                 ->normalizeKeys(false)
                 // ->useAttributeAsKey('name', false)
                 ->defaultValue(array())
                 ->info('The list of entities to manage in the administration zone.')
                 ->prototype('variable')
                 ->end()
//            ->booleanNode('unicorns_are_real')->defaultTrue()->end()
//            ->integerNode('min_sunshine')->defaultValue(3)->end()
                 ->end()
             ;
    }


    public function build(ContainerBuilder $container)
    {
//        $container->addCompilerPass(new SurvosBaseCompilerPass());
    }

}
