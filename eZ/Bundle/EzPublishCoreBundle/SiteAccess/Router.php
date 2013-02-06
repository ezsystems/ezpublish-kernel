<?php
/**
 * File containing the Siteaccess Router class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\SiteAccess;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Router as BaseRouter;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RuntimeException;

class Router extends BaseRouter implements ContainerAwareInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    public function setContainer( ContainerInterface $container = null )
    {
        $this->container = $container;
    }

    /**
     * Builds siteaccess matcher.
     * If $matchingClass begins with "@", it will be considered as a service identifier and loaded with the service container.
     *
     * @param $matchingClass
     * @param $matchingConfiguration
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest $request
     *
     * @return \eZ\Bundle\EzPublishCoreBundle\SiteAccess\Matcher
     *
     * @throws \RuntimeException
     */
    protected function buildMatcher( $matchingClass, $matchingConfiguration, SimplifiedRequest $request )
    {
        if ( $matchingClass[0] === '@' )
        {
            /** @var $matcher \eZ\Bundle\EzPublishCoreBundle\SiteAccess\Matcher */
            $matcher = $this->container->get( substr( $matchingClass, 1 ) );
            if ( !$matcher instanceof Matcher )
                throw new RuntimeException( 'A service based siteaccess matcher MUST implement eZ\\Bundle\\EzPublishCoreBundle\\SiteAccess\\Matcher interface.' );

            $matcher->setMatchingConfiguration( $matchingConfiguration );
            $matcher->setRequest( $request );
            return $matcher;
        }

        return parent::buildMatcher( $matchingClass, $matchingConfiguration, $request );
    }
}
