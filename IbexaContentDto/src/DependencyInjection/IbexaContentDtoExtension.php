<?php

namespace Kaliop\IbexaContentDto\DependencyInjection;

use ReflectionClass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 *
 */
class IbexaContentDtoExtension extends Extension
{
    public const YML_PATH = '%s/../Resources/config/%s';

    protected static array $filesToLoad = [
        'services.yaml'
    ];

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     *
     * @return void
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $dir = $this->getDir();
        $loader = new YamlFileLoader($container, new FileLocator($dir . '/../Resources/config'));
        foreach (self::$filesToLoad as $serviceFile) {
            $loader->load($serviceFile);
        }
    }

    /**
     * @return string
     */
    private function getDir(): string
    {
        $reflector = new ReflectionClass(get_class($this));
        $filename = $reflector->getFileName();

        return dirname($filename);
    }
}
