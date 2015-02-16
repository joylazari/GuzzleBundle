<?php

namespace Kopjra\GuzzleBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * {@inheritdoc}
 */
class ServicesCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $subscribers = $container->findTaggedServiceIds('kpj_guzzle.service.description');
        $serviceClass = $container->getParameter('kpj_guzzle.service.class');

        foreach ($subscribers as $id => $attrs) {
            $subscribers[$id]['name'] = isset($attrs['name'])
                ? 'kpj_guzzle.services.'.$attrs['name']
                : 'kpj_guzzle.services.'.$id;
        }

        foreach ($subscribers as $id => $attrs) {
            $definition = new Definition($serviceClass, [
                new Reference('kpj_guzzle'),
                new Reference($id),
            ]);

            $container->createService($definition, $attrs['name']);
        }
    }
}
