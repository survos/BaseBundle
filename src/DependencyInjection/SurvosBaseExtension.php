<?php

namespace Survos\BaseBundle\DependencyInjection;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class SurvosBaseExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $definition = $container->getDefinition('survos_base_bundle.base_service');

        $definition->setArgument(0, $config['routes']);


        // likely these can be combined
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

//        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config/container'));
//        $loader->load('knp-menu.yml');

        /* @todo: add menu items based on what bundles are installed (EasyAdminBundle, etc.) */
        $bundles = $container->getParameter('kernel.bundles');

        foreach (['KnpUOAuth2ClientBundle'] as $bundleName) {
            if (!isset($bundles[$bundleName])) {
                throw new \InvalidArgumentException(
                    "The bundle $bundleName needs to be registered in order to use ".  __CLASS__
                );
                $def = $container->findDefinition('oauth2.registry');
                dd($def, $container);
            }
        }

        // dd($bundles); die();

        // $configManager = $container->get('easyadmin.config.manager');
        // $definition->setArgument(1, $configManager);
        // $definition->setArgument(1, $config['min_sunshine']);
    }

    public function getAlias(): string
    {
        return 'survos_base';
    }
}
