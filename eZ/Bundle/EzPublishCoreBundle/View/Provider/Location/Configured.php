<?php
/**
 * File containing the Configured Location View Provider class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\View\Provider\Location;

use eZ\Publish\Core\MVC\Symfony\View\Provider\Location\Configured as BaseConfigured;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\API\Repository\Repository;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct( ConfigResolverInterface $configResolver, Repository $repository, ContainerInterface $container )
    {
        $this->container = $container;

        parent::__construct( $repository, $configResolver->getParameter( 'location_view' ) );
    }

    /**
     * Returns the matcher object either from a service identifier or from a class.
     *
     * @param string $matcherIdentifier If it is a service identifier, the matcher will be built with the service container.
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher
     */
    protected function getMatcher( $matcherIdentifier )
    {
        if ( $this->container->has( $matcherIdentifier ) )
            return $this->container->get( $matcherIdentifier );

        return parent::getMatcher( $matcherIdentifier );
    }
}
