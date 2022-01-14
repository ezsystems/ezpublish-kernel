<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Publish\Core\MVC\Symfony\Security\Authentication\AnonymousAuthenticationProvider;
use eZ\Publish\Core\MVC\Symfony\Security\Authentication\DefaultAuthenticationSuccessHandler;
use eZ\Publish\Core\MVC\Symfony\Security\Authentication\RememberMeRepositoryAuthenticationProvider;
use eZ\Publish\Core\MVC\Symfony\Security\Authentication\RepositoryAuthenticationProvider;
use eZ\Publish\Core\MVC\Symfony\Security\HttpUtils;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Security related compiler pass.
 * Manipulates Symfony core security services to adapt them to eZ security needs.
 */
class SecurityPass implements CompilerPassInterface
{
    public const CONSTANT_AUTH_TIME_SETTING = 'ibexa.security.authentication.constant_auth_time';

    public const CONSTANT_AUTH_TIME_DEFAULT = 1.0;

    public function process(ContainerBuilder $container)
    {
        if (!($container->hasDefinition('security.authentication.provider.dao') &&
              $container->hasDefinition('security.authentication.provider.rememberme') &&
              $container->hasDefinition('security.authentication.provider.anonymous'))) {
            return;
        }

        $configResolverRef = new Reference('ezpublish.config.resolver');
        $repositoryReference = new Reference('ezpublish.api.repository');
        $loggerReference = new Reference('logger');

        // Override and inject the Repository in the authentication provider.
        // We need it for checking user credentials
        $daoAuthenticationProviderDef = $container->findDefinition('security.authentication.provider.dao');
        $daoAuthenticationProviderDef->setClass(RepositoryAuthenticationProvider::class);
        $daoAuthenticationProviderDef->addMethodCall(
            'setRepository',
            [$repositoryReference]
        );
        $daoAuthenticationProviderDef->addMethodCall(
            'setConstantAuthTime',
            [
                $container->hasParameter(self::CONSTANT_AUTH_TIME_SETTING) ?
                (float)$container->getParameter(self::CONSTANT_AUTH_TIME_SETTING) :
                self::CONSTANT_AUTH_TIME_DEFAULT,
            ]
        );
        $daoAuthenticationProviderDef->addMethodCall(
            'setLogger',
            [$loggerReference]
        );

        $rememberMeAuthenticationProviderDef = $container->findDefinition('security.authentication.provider.rememberme');
        $rememberMeAuthenticationProviderDef->setClass(RememberMeRepositoryAuthenticationProvider::class);
        $rememberMeAuthenticationProviderDef->addMethodCall(
            'setRepository',
            [$repositoryReference]
        );

        $anonymousAuthenticationProviderDef = $container->findDefinition('security.authentication.provider.anonymous');
        $anonymousAuthenticationProviderDef->setClass(AnonymousAuthenticationProvider::class);
        $anonymousAuthenticationProviderDef->addMethodCall(
            'setRepository',
            [$repositoryReference]
        );

        $anonymousAuthenticationProviderDef->addMethodCall(
            'setConfigResolver',
            [$configResolverRef]
        );

        if (!$container->hasDefinition('security.http_utils')) {
            return;
        }

        $httpUtilsDef = $container->findDefinition('security.http_utils');
        $httpUtilsDef->setClass(HttpUtils::class);
        $httpUtilsDef->addMethodCall(
            'setSiteAccess',
            [new Reference('ezpublish.siteaccess')]
        );

        if (!$container->hasDefinition('security.authentication.success_handler')) {
            return;
        }

        $successHandlerDef = $container->getDefinition('security.authentication.success_handler');
        $successHandlerDef->setClass(DefaultAuthenticationSuccessHandler::class);
        $successHandlerDef->addMethodCall(
            'setConfigResolver',
            [$configResolverRef]
        );
    }
}
