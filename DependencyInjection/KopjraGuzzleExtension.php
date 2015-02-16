<?php

namespace Kopjra\GuzzleBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class KopjraGuzzleExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.xml');

        // Twig extension is loaded only if it's enabled
        // If twig isn't available then this extension isn't loaded
        if ($config['twig']['enabled'] && $container->hasDefinition('twig')) {
            $loader->load('twig.xml');
        }

        // Load subscribers sections
        $this->loadSubscribers($config['subscribers'], $container);

        // Replace the emitter with a new one because
        // the framework doesn't allow calls on getters
        $guzzle = $container->getDefinition('kpj_guzzle');

        $config['client']['emitter'] = new Reference('kpj_guzzle.event.emitter');

        $guzzle->replaceArgument(0, $config['client']);
    }

    /**
     * For each subscriber, if enabled, load services and parameters.
     *
     * @param array            $config    Subscribers section only.
     * @param ContainerBuilder $container Container builder.
     */
    private function loadSubscribers(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config/subscribers')
        );

        // Cache is loaded only if it's enabled
        if ($config['cache']['enabled']) {
            $loader->load('cache.xml');
        }

        // Logger is loaded only if it's enabled
        // If monolog isn't available then logger isn't loaded
        // http://slides.seld.be/?file=2011-10-20+High+Performance+Websites+with+Symfony2.html#33
        if ($config['log']['enabled'] && $container->hasDefinition('logger')) {
            $loader->load('log.xml');
        }

        // OAuth is loaded only if it's enabled
        if ($config['oauth']['enabled']) {
            $loader->load('oauth.xml');
        }

        // Retry system is loaded only if it's enabled
        if ($config['retry']['enabled']) {
            $loader->load('retry.xml');
        }
    }
}
