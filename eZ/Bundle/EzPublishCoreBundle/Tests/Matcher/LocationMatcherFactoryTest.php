<?php
/**
 * File containing the LocationMatcherFactoryTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Matcher;

use eZ\Bundle\EzPublishCoreBundle\Matcher\LocationMatcherFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LocationMatcherFactoryTest extends BaseMatcherFactoryTest
{
    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\Matcher\LocationMatcherFactory::__construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\Matcher\LocationMatcherFactory::getMatcher
     * @covers \eZ\Publish\Core\MVC\Symfony\Matcher\LocationMatcherFactory::doMatch
     * @covers eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory::match
     */
    public function testGetMatcherForLocation()
    {
        $matcherServiceIdentifier = 'my.matcher.service';
        $resolverMock = $this->getResolverMock( $matcherServiceIdentifier );
        $container = $this->getMock( 'Symfony\\Component\\DependencyInjection\\ContainerInterface' );
        $container
            ->expects( $this->atLeastOnce() )
            ->method( 'has' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( $matcherServiceIdentifier, true )
                    )
                )
            );
        $container
            ->expects( $this->atLeastOnce() )
            ->method( 'get' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'ezpublish.api.repository', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->getMock( 'eZ\\Publish\\API\\Repository\\Repository' ) ),
                        array( 'ezpublish.config.resolver', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $resolverMock ),
                        array( $matcherServiceIdentifier, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\ContentBased\\MatcherInterface' ) ),
                    )
                )
            );

        $matcherFactory = new LocationMatcherFactory( $container );
        $matcherFactory->match( $this->getLocationMock(), 'full' );
    }
}
