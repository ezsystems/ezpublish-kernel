<?php

/**
 * File containing the SecurityPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Security related compiler pass.
 * Manipulates Symfony core security services to adapt them to eZ security needs.
 */
class SecurityPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!($container->hasDefinition('security.authentication.provider.dao') && $container->hasDefinition('security.authentication.provider.anonymous'))) {
            return;
        }

        $configResolverRef = new Reference('ezpublish.config.resolver');
        $repositoryReference = new Reference('ezpublish.api.repository');
        // Inject the Repository in the authentication provider.
        // We need it for checking user credentials
        $daoAuthenticationProviderDef = $container->findDefinition('security.authentication.provider.dao');
        $daoAuthenticationProviderDef->addMethodCall(
            'setRepository',
            array($repositoryReference)
        );

        $anonymousAuthenticationProviderDef = $container->findDefinition('security.authentication.provider.anonymous');
        $anonymousAuthenticationProviderDef->addMethodCall(
            'setRepository',
            array($repositoryReference)
        );

        $anonymousAuthenticationProviderDef->addMethodCall(
            'setConfigResolver',
            array($configResolverRef)
        );

        if (!$container->hasDefinition('security.http_utils')) {
            return;
        }

        $httpUtilsDef = $container->findDefinition('security.http_utils');
        $httpUtilsDef->addMethodCall(
            'setSiteAccess',
            array(new Reference('ezpublish.siteaccess'))
        );

        if (!$container->hasDefinition('security.authentication.success_handler')) {
            return;
        }

        $successHandlerDef = $container->getDefinition('security.authentication.success_handler');
        $successHandlerDef->addMethodCall(
            'setConfigResolver',
            array($configResolverRef)
        );
    }
}
