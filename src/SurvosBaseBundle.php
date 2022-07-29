<?php
namespace Survos\BaseBundle;

use Survos\BaseBundle\Controller\OAuthController;
use Survos\BaseBundle\DependencyInjection\Compiler\SurvosBaseCompilerPass;
use Survos\BaseBundle\Event\KnpMenuEvent;
use Survos\BaseBundle\Menu\MenuBuilder;
use Survos\BaseBundle\Security\BaseAuthenticator;
use Survos\BaseBundle\Twig\TwigExtensions;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class SurvosBaseBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $serviceId = 'survos_base.base';
        $builder->register($serviceId, BaseService::class)
            ->setArgument('$userClass', $config['user_class'])
            ->setArgument('$clientRegistry', new Reference('oauth2.registry'))
            ->setArgument('$provider', new Reference('knpu.oauth2.provider_factory'))
            ->setPublic(true)
            ->setAutowired(true);

        $builder
            ->setDefinition('survos.base_twig', new Definition(TwigExtensions::class))
            ->addTag('twig.extension')
            ->setPublic(false)
        ;

        $container->services()->alias(BaseService::class, $serviceId);

//        $builder->autowire(SurvosWorkflowDumpCommand::class)
//            ->addTag('console.command')
//        ;

        $builder->autowire(MenuBuilder::class)
            ->setArgument('$factory', new Reference('knp_menu.factory'))
            ->setArgument('$eventDispatcher', new Reference('event_dispatcher'))
            ->addTag('knp_menu.menu_builder', ['method' => 'createSidebarMenu', 'alias' => 'survos_sidebar_menu'])
            ->addTag('knp_menu.menu_builder', ['method' => 'createPageMenu', 'alias' => 'survos_page_menu'])
            ->addTag('knp_menu.menu_builder', ['method' => 'createNavbarMenu', 'alias' => KnpMenuEvent::NAVBAR_MENU_EVENT])
            ->addTag('knp_menu.menu_builder', ['method' => 'createNavbarMenu', 'alias' => 'survos_navbar_menu'])
            ;

        $definition = $builder->autowire(OAuthController::class)
            ->addArgument(new Reference($serviceId))
            ->addArgument('$registry', new Reference('doctrine'))
            ->setArgument('$router', new Reference('router'))
            ->setArgument('$userClass', $config['user_class'])
            ->setArgument('$clientRegistry', new Reference('knpu.oauth2.registry'))
            ->addTag('container.service_subscriber')
            ->addTag('controller.service_argument')
            ->setPublic(true);

        if ($userProviderServiceId = $config['user_provider']) {
            $definition
                ->addMethodCall('setUserProvider', [new Reference($userProviderServiceId)])
            ;

        }

        $definition = $builder->autowire(BaseAuthenticator::class)
            ->addArgument('$registry', new Reference('doctrine'))
            ->setArgument('$router', new Reference('router'))
            ->setArgument('$clientRegistry', new Reference('knpu.oauth2.registry'))
            ->addTag('container.service_subscriber')
            ->addTag('controller.service_argument')
            ->setPublic(true);

        if ($userProviderServiceId = $config['user_provider']) {
            $definition
                ->addMethodCall('setUserProvider', [new Reference($userProviderServiceId)])
            ;

        }


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
            ->scalarNode('user_provider')
                ->info('Class for the authenticated user')
                ->defaultValue(null)
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
