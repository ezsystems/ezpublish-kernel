<?php
/**
 * File containing the ConfiguredTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Tests\Provider\Block;

use eZ\Publish\Core\MVC\Symfony\View\Provider\Block\Configured as BlockViewProvider;

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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getBlockMock()
    {
        return $this
            ->getMockBuilder( 'eZ\\Publish\\Core\\FieldType\\Page\\Parts\\Block' )
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Configured::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Block\Configured::getView
     */
    public function testGetViewContentFail()
    {
        $this->matcherFactoryMock
            ->expects( $this->once() )
            ->method( 'match' )
            ->will( $this->returnValue( null ) );

        $cvp = new BlockViewProvider( $this->matcherFactoryMock );
        $this->assertNull(
            $cvp->getView(
                $this->getBlockMock(),
                'block'
            )
        );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Configured::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Configured::buildContentView
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Block\Configured::getView
     *
     * @expectedException InvalidArgumentException
     */
    public function testGetViewContentNoTemplate()
    {
        $this->matcherFactoryMock
            ->expects( $this->once() )
            ->method( 'match' )
            ->will( $this->returnValue( array( 'match' => array() ) ) );

        $cvp = new BlockViewProvider( $this->matcherFactoryMock );
        $this->assertNull(
            $cvp->getView(
                $this->getBlockMock(),
                'full'
            )
        );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Configured::__construct
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Configured::buildContentView
     * @covers \eZ\Publish\Core\MVC\Symfony\View\Provider\Block\Configured::getView
     */
    public function testGetViewContent()
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

        $cvp = new BlockViewProvider( $this->matcherFactoryMock );
        $view = $cvp->getView(
            $this->getBlockMock(),
            'full'
        );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\ContentView', $view );
        $this->assertSame( $configHash, $view->getConfigHash() );
        $this->assertSame( $template, $view->getTemplateIdentifier() );
        $this->assertSame( array(), $view->getParameters() );
    }
}
