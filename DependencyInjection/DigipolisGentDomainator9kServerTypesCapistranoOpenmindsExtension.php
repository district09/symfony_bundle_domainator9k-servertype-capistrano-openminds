<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class DigipolisGentDomainator9kServerTypesCapistranoOpenmindsExtension
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\DependencyInjection
 */
class DigipolisGentDomainator9kServerTypesCapistranoOpenmindsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }
}
