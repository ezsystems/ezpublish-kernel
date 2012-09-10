<?php
/**
 * File containing the Configured ContentViewProvider class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\View\ContentViewProvider;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured as BaseConfigured,
    eZ\Publish\Core\MVC\Symfony\SiteAccess,
    eZ\Publish\API\Repository\Repository,
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
     * @todo Instead of using the container to retrieve the matching config, it would be better to get it with some ConfigResolver object
     *
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess $siteAccess
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct( SiteAccess $siteAccess, Repository $repository, ContainerInterface $container )
    {
        $this->container = $container;

        $locationMatchConfig = array();
        if ( $this->container->hasParameter( "ezpublish.location_view.$siteAccess->name" ) )
        {
            $locationMatchConfig = $this->container->getParameter( "ezpublish.location_view.$siteAccess->name" );
        }

        $contentMatchConfig = array();
        if ( $this->container->hasParameter( "ezpublish.content_view.$siteAccess->name" ) )
        {
            $contentMatchConfig = $this->container->getParameter( "ezpublish.content_view.$siteAccess->name" );
        }

        parent::__construct( $repository, $locationMatchConfig, $contentMatchConfig );
    }

    /**
     * Returns the matcher object either from a service identifier or from a class.
     *
     * @param string $matcherIdentifier If it is a service identifier, the matcher will be built with the service container.
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher
     */
    protected function getMatcher( $matcherIdentifier )
    {
        if ( $this->container->has( $matcherIdentifier ) )
            return $this->container->get( $matcherIdentifier );

        return parent::getMatcher( $matcherIdentifier );
    }
}
