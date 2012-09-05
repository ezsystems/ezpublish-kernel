<?php
/**
 * File containing the ConfiguredContentViewProviderTest class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests;

use eZ\Publish\Core\MVC\Symfony\View\Tests\ContentViewProvider\Configured\BaseTest,
    eZ\Publish\Core\MVC\Symfony\SiteAccess,
    eZ\Bundle\EzPublishCoreBundle\View\ContentViewProvider\Configured;

class ConfiguredContentViewProviderTest extends BaseTest
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
     * @covers \eZ\Bundle\EzPublishCoreBundle\View\ContentViewProvider\Configured::__construct
     * @covers \eZ\Bundle\EzPublishCoreBundle\View\ContentViewProvider\Configured::getMatcher
     */
    public function testGetMatcherForLocation()
    {
        $matcherServiceIdentifier = 'my.matcher.service';
        $container = $this->getContainerMock( $matcherServiceIdentifier );

        // The following should happen in getMatcher()
        $container
            ->expects( $this->once() )
            ->method( 'has' )
            ->with( $matcherServiceIdentifier )
            ->will( $this->returnValue( true ) )
        ;
        $container->expects( $this->once() )
            ->method( 'get' )
            ->with( $matcherServiceIdentifier )
            ->will(
                $this->returnValue( $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\ContentViewProvider\\Configured\\Matcher' ) )
            )
        ;

        $cvp = new Configured( $this->siteAccess, $this->repositoryMock, $container );
        $cvp->getViewForLocation(
            $this->getLocationMock(),
            'full'
        );
    }

    /**
     * Returns a properly configured DIC instance.
     *
     * @param $matcherServiceIdentifier
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContainerMock( $matcherServiceIdentifier )
    {
        $container = $this->getMock( 'Symfony\\Component\\DependencyInjection\\ContainerInterface' );
        $configIdConstraint = $this->logicalAnd(
            $this->stringStartsWith( 'ezpublish.' ),
            $this->stringEndsWith( '_view.' . $this->siteAccess->name )
        );
        $container
            ->expects( $this->atLeastOnce() )
            ->method( 'hasParameter' )
            ->with( $configIdConstraint )
            ->will( $this->returnValue( true ) )
        ;
        $container
            ->expects( $this->atLeastOnce() )
            ->method( 'getParameter' )
            ->with( $configIdConstraint )
            ->will(
                $this->returnValue(
                    array(
                        'matchRule' => array(
                            'viewType'         => 'full',
                            'matchTemplate'    => 'my_template.html.twig',
                            'match'            => array(
                                $matcherServiceIdentifier   => 'someValue'
                            )
                        )
                    )
                )
            )
        ;

        return $container;
    }
}
