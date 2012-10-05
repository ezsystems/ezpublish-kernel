<?php
/**
 * File containing the REST security Factory class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\HttpBasicFactory as BaseHttpBasicFactory,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * Basic auth based authentication provider, working with eZ Publish repository
 */
class HttpBasicFactory extends BaseHttpBasicFactory
{
    const AUTHENTICATION_PROVIDER_ID = 'ezpublish.security.authentication_provider.basic';

    public function create( ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint )
    {
        list( $provider, $listenerId, $entryPointId ) = parent::create( $container, $id, $config, $userProvider, $defaultEntryPoint );

        // We only need to redefine the authentication provider
        unset( $provider );
        $provider = self::AUTHENTICATION_PROVIDER_ID . ".$id";
        $container
            ->setDefinition( $provider, new DefinitionDecorator( self::AUTHENTICATION_PROVIDER_ID ) )
            ->replaceArgument( 2, $id )
        ;

        return array( $provider, $listenerId, $entryPointId );
    }

    public function getKey()
    {
        return 'ezpublish_http_basic';
    }
}
