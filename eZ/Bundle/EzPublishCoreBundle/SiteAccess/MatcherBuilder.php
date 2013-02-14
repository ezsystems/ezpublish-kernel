<?php
/**
 * File containing the Siteaccess MatcherBuilder class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\SiteAccess;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\MatcherBuilder as BaseMatcherBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use RuntimeException;

/**
 * Siteaccess matcher builder based on services.
 */
class MatcherBuilder extends BaseMatcherBuilder
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    public function __construct( ContainerInterface $container )
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
     *
     */
    public function buildMatcher( $matchingClass, $matchingConfiguration, SimplifiedRequest $request )
    {
        if ( $matchingClass[0] === '@' )
        {
            /** @var $matcher \eZ\Bundle\EzPublishCoreBundle\SiteAccess\Matcher */
            $matcher = $this->container->get( substr( $matchingClass, 1 ) );
            if ( !$matcher instanceof Matcher )
                throw new RuntimeException( 'A service based siteaccess matcher MUST implement ' . __NAMESPACE__ . '\\Matcher interface.' );

            $matcher->setMatchingConfiguration( $matchingConfiguration );
            $matcher->setRequest( $request );
            return $matcher;
        }

        return parent::buildMatcher( $matchingClass, $matchingConfiguration, $request );
    }
}
