<?php

/**
 * File containing the IdentityDefinerPas class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class IdentityDefinerPass
 *
Use FOSHttpCacheBundle user context feature instead. Will be removed in future 7.x FT release.
 */
class IdentityDefinerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.user.hash_generator')) {
            return;
        }

        $hashGeneratorDef = $container->getDefinition('ezpublish.user.hash_generator');
        foreach ($container->findTaggedServiceIds('ezpublish.identity_definer') as $id => $attributes) {
            $hashGeneratorDef->addMethodCall(
                'setIdentityDefiner',
                array(new Reference($id))
            );
        }
    }
}
