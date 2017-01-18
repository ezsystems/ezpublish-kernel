<?php

/**
 * File containing the TranslationCollectorPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\Translation\GlobCollector;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This compilation pass loads every ezplatform available translations into symfony translator.
 */
class TranslationCollectorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('translator.default')) {
            return;
        }

        $definition = $container->getDefinition('translator.default');
        $collector = new GlobCollector($container->getParameterBag()->get('kernel.root_dir'));

        foreach ($collector->collect() as $file) {
            $definition->addMethodCall(
                'addResource',
                array($file['format'], $file['file'], $file['locale'], $file['domain'])
            );
        }
    }
}
