<?php
namespace Survos\BaseBundle;

use Survos\BaseBundle\Controller\OAuthController;
use Survos\BaseBundle\DependencyInjection\Compiler\SurvosBaseCompilerPass;
use Survos\BaseBundle\Twig\TwigExtensions;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SurvosBaseBundle extends AbstractBundle
{
//    public function getPath(): string {
//        return __DIR__;
//    }
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $serviceId = 'survos_base.base';
        $builder->register($serviceId, BaseService::class)
            ->setArgument('$userClass', $config['user_class'])
            ->setArgument('$clientRegistry', new Reference('oauth2.registry'))
            ->setArgument('$provider', new Reference('knpu.oauth2.provider_factory'))
            ->setPublic(true)
            ->setAutowired(true);

        $container->services()->alias(BaseService::class, $serviceId);

        $definition = $builder
            ->autowire('survos.base_twig', TwigExtensions::class)
            ->addTag('twig.extension')
        ;

//        $builder->autowire(SurvosWorkflowDumpCommand::class)
//            ->addTag('console.command')
//        ;

        $builder->autowire(OAuthController::class)
            ->addArgument(new Reference($serviceId))
            ->addArgument(new Reference('doctrine'))
            ->addArgument(new Reference('router'))
            ->addArgument(new Reference('knpu.oauth2.registry'))
            ->addTag('container.service_subscriber')
            ->addTag('controller.service_argument')
            ->setPublic(true)
        ;

    $container->import('../config/services.xml');



    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
                 ->children()
                 ->scalarNode('theme')
                 ->info('the theme code for rendering templates')
                 ->end()
            ->scalarNode('user_class')
            ->info('Class for the authenticated user')
            ->defaultValue('App\Entity\User')
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
