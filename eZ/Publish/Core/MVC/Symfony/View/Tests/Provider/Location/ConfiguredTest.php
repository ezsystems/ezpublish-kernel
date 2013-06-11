<?php
/**
 * File containing the ConfiguredTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\Provider\Location;

use eZ\Publish\Core\MVC\Symfony\View\Provider\Location\Configured as LocationViewProvider;

class ConfiguredTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $matcherFactoryMock;

    protected function setUp()
    {
        parent::setUp();
        $this->matcherFactoryMock = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\Matcher\\MatcherFactoryInterface' );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Configured::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Location\Configured::getView
     */
    public function testGetViewLocationFail()
    {
        $this->matcherFactoryMock
            ->expects( $this->once() )
            ->method( 'match' )
            ->will( $this->returnValue( null ) );

        $lvp = new LocationViewProvider( $this->matcherFactoryMock );
        $this->assertNull(
            $lvp->getView(
                $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' ),
                'full'
            )
        );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Configured::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Configured::buildContentView
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Location\Configured::getView
     *
     * @expectedException InvalidArgumentException
     */
    public function testGetViewLocationNoTemplate()
    {
        $this->matcherFactoryMock
            ->expects( $this->once() )
            ->method( 'match' )
            ->will( $this->returnValue( array( 'match' => array() ) ) );

        $lvp = new LocationViewProvider( $this->matcherFactoryMock );
        $this->assertNull(
            $lvp->getView(
                $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' ),
                'full'
            )
        );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Configured::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Configured::buildContentView
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Location\Configured::getView
     */
    public function testGetViewLocation()
    {
        $template = 'my_template.html.twig';
        $configHash = array(
            'match' => array(),
            'template' => $template
        );
        $this->matcherFactoryMock
            ->expects( $this->once() )
            ->method( 'match' )
            ->will( $this->returnValue( $configHash ) );

        $lvp = new LocationViewProvider( $this->matcherFactoryMock );
        $view = $lvp->getView(
            $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Location' ),
            'full'
        );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\ContentView', $view );
        $this->assertSame( $configHash, $view->getConfigHash() );
        $this->assertSame( $template, $view->getTemplateIdentifier() );
        $this->assertSame( array(), $view->getParameters() );
    }
}
