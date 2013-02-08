<?php
/**
 * File containing the ConfiguredContentViewProviderTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests;

use eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\BaseTest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Bundle\EzPublishCoreBundle\View\Provider\Location\Configured;

class ConfiguredLocationViewProviderTest extends BaseTest
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    private $siteAccess;

    protected function setUp()
    {
        parent::setUp();
        $this->siteAccess = new SiteAccess( 'test' );
    }

    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\View\Provider\Location\Configured::__construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\View\Provider\Location\Configured::getMatcher
     */
    public function testGetMatcherForLocation()
    {
        $matcherServiceIdentifier = 'my.matcher.service';
        $container = $this->getMock( 'Symfony\\Component\\DependencyInjection\\ContainerInterface' );

        // The following should happen in getMatcher()
        $container
            ->expects( $this->once() )
            ->method( 'has' )
            ->with( $matcherServiceIdentifier )
            ->will( $this->returnValue( true ) );
        $container->expects( $this->once() )
            ->method( 'get' )
            ->with( $matcherServiceIdentifier )
            ->will(
                $this->returnValue( $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\ContentViewProvider\\Configured\\Matcher' ) )
            );

        $resolverMock = $this->getResolverMock( $matcherServiceIdentifier );
        $lvp = new Configured( $resolverMock, $this->repositoryMock, $container );
        $lvp->getView(
            $this->getLocationMock(),
            'full'
        );
    }

    /**
     * @param string $matcherServiceIdentifier
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getResolverMock( $matcherServiceIdentifier )
    {
        $resolverMock = $this->getMock( 'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface' );
        $resolverMock
            ->expects( $this->atLeastOnce() )
            ->method( 'getParameter' )
            ->with( $this->logicalOr( 'location_view', 'content_view' ) )
            ->will(
                $this->returnValue(
                    array(
                        'full' => array(
                            'matchRule' => array(
                                'template'    => 'my_template.html.twig',
                                'match'            => array(
                                    $matcherServiceIdentifier   => 'someValue'
                                )
                            )
                        )
                    )
                )
            );

        return $resolverMock;
    }
}
