<?php

namespace Survos\BaseBundle;

use Survos\BaseBundle\DependencyInjection\Compiler\SurvosBaseCompilerPass;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\WebpackEncoreBundle\Twig\StimulusTwigExtension;
use Twig\Environment;

class SurvosBaseBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return __DIR__;
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SurvosBaseCompilerPass());
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if (0)
            if (class_exists(Environment::class) && class_exists(StimulusTwigExtension::class)) {
                $builder
                    ->setDefinition('survos.datatables_bundle', new Definition(DatatablesTwigExtension::class))
                    ->addArgument(new Reference('serializer'))
                    ->addArgument(new Reference('serializer.normalizer.object'))
                    ->addArgument(new Reference('router.default'))
                    ->addArgument(new Reference('api_platform.iri_converter'))
                    ->addArgument(new Reference('webpack_encore.twig_stimulus_extension'))
                    ->addTag('twig.extension')
                    ->setPublic(false);
            }

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
                ->end();
        }


    }
