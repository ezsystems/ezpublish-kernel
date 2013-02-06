<?php
/**
 * File containing the Security Factory class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class Factory extends AbstractFactory
{
    const AUTHENTICATION_PROVIDER_ID = 'ezpublish.security.authentication_provider';
    const AUTHENTICATION_LISTENER_ID = 'ezpublish_legacy.security.firewall_listener';

    /**
     * Subclasses must return the id of a service which implements the
     * AuthenticationProviderInterface.
     *
     * @param ContainerBuilder $container
     * @param string $id The unique id of the firewall
     * @param array $config The options array for this listener
     * @param string $userProviderId The id of the user provider
     *
     * @return string never null, the id of the authentication provider
     */
    protected function createAuthProvider( ContainerBuilder $container, $id, $config, $userProviderId )
    {
        $providerId = self::AUTHENTICATION_PROVIDER_ID . ".$id";
        $container
            ->setDefinition( $providerId, new DefinitionDecorator( self::AUTHENTICATION_PROVIDER_ID ) )
            ->replaceArgument( 0, new Reference( $userProviderId ) )
            ->addArgument( $id );

        return $providerId;
    }

    protected function createListener( $container, $id, $config, $userProvider )
    {
        $parentListenerId = $this->getListenerId();
        $listenerId = "$parentListenerId.$id";
        $container
            ->setDefinition( $listenerId, new DefinitionDecorator( $parentListenerId ) )
            ->replaceArgument( 2, $id );

        return $listenerId;
    }

    /**
     * Subclasses must return the id of the listener template.
     *
     * @return string
     */
    protected function getListenerId()
    {
        return self::AUTHENTICATION_LISTENER_ID;
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'ezpublish';
    }
}
