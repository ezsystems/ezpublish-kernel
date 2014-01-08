<?php
/**
 * File containing the SecurityPass class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
    public function process( ContainerBuilder $container )
    {
        if ( !( $container->hasDefinition( 'security.authentication.provider.dao' ) && $container->hasDefinition( 'security.authentication.provider.anonymous' ) ) )
        {
            return;
        }

        $lazyRepositoryReference = new Reference( 'ezpublish.api.repository.lazy' );
        // Inject the Repository in the authentication provider.
        // We need it for checking user credentials
        $daoAuthenticationProviderDef = $container->findDefinition( 'security.authentication.provider.dao' );
        $daoAuthenticationProviderDef->addMethodCall(
            'setLazyRepository',
            array( $lazyRepositoryReference )
        );

        $anonymousAuthenticationProviderDef = $container->findDefinition( 'security.authentication.provider.anonymous' );
        $anonymousAuthenticationProviderDef->addMethodCall(
            'setLazyRepository',
            array( $lazyRepositoryReference )
        );
    }
}
