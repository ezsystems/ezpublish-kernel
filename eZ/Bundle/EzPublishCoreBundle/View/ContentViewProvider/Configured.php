<?php
/**
 * File containing the Configured ContentViewProvider class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\View\ContentViewProvider;

use eZ\Publish\MVC\View\ContentViewProvider\Configured as BaseConfigured,
    Symfony\Component\DependencyInjection\ContainerInterface;

class Configured extends BaseConfigured
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     * Will get the matching configuration from the service container dynamically, with the siteaccess name.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct( ContainerInterface $container )
    {
        $this->container = $container;

        $siteAccess = $this->container->get( 'ezpublish.siteaccess' );
        $locationMatchConfig = array();
        if ( $this->container->hasParameter( "ezpublish.location_view.$siteAccess->name" ) )
        {
            $locationMatchConfig = $this->container->getParameter( "ezpublish.location_view.$siteAccess->name" );
        }

        $contentMatchConfig = array();
        if ( $this->container->hasParameter( "ezpublish.content_view.$siteAccess->name" ) )
        {
            $locationMatchConfig = $this->container->getParameter( "ezpublish.content_view.$siteAccess->name" );
        }

        parent::__construct( $this->container->get( 'ezpublish.api.repository' ), $locationMatchConfig, $contentMatchConfig );
    }

    /**
     * Returns the matcher object either from a service identifier or from a class.
     *
     * @param string $matcherIdentifier If it is a service identifier, the matcher will be built with the service container.
     * @return \eZ\Publish\MVC\View\ContentViewProvider\Configured\Matcher
     */
    protected function getMatcher( $matcherIdentifier )
    {
        if ( $this->container->has( $matcherIdentifier ) )
            return $this->container->get( $matcherIdentifier );

        return parent::getMatcher( $matcherIdentifier );
    }
}
