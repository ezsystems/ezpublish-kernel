<?php
/**
 * File containing the ViewTemplateListenerTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Templating;

use eZ\Bundle\EzPublishCoreBundle\EventListener\ViewTemplateListener;
use eZ\Bundle\EzPublishCoreBundle\Templating\ParameterWrapper;
use eZ\Publish\Core\MVC\Symfony\Event\PreContentViewEvent;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ViewTemplateListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\EventListener\ViewTemplateListener::onPreContentView
     */
    public function testOnPreContentViewNoParameter()
    {
        $contentView = $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\ContentView' );
        $contentView
            ->expects( $this->once() )
            ->method( 'getConfigHash' )
            ->will( $this->returnValue( array() ) );
        $contentView
            ->expects( $this->never() )
            ->method( 'addParameters' );

        $listener = new ViewTemplateListener( $this->getMock( 'Symfony\\Component\\DependencyInjection\\ContainerInterface' ) );
        $listener->onPreContentView( new PreContentViewEvent( $contentView ) );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\EventListener\ViewTemplateListener::onPreContentView
     */
    public function testOnPreContentView()
    {
        $params = array(
            'foo'           => 'bar',
            'some'          => 'thing',
            'osTypes'       => array( 'osx', 'linux', 'losedows' ),
            // $viewParameterProvider1
            'my_service'    => array(
                'service' => 'some_defined_service'
            ),
            // $viewParameterProvider2
            'other_service' => array(
                'service'   => 'another_service',
                'method'    => 'getFoo'
            )
        );

        $contentView = new ContentView;
        $contentView->setConfigHash( array( 'params' => $params ) );

        $viewParameterProvider1Values = array(
            'some' => 'thing',
            'truc' => array( 'muche' )
        );
        $viewParameterProvider1 = $this->getMock( 'eZ\\Bundle\\EzPublishCoreBundle\\Templating\\ViewParameterProvider' );
        $viewParameterProvider1
            ->expects( $this->once() )
            ->method( 'getContentViewParameters' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\Core\\MVC\\Symfony\\View\\ContentViewInterface' ) )
            ->will(
                $this->returnValue( $viewParameterProvider1Values )
            );

        $viewParameterProvider2Values = array( 'foo' => 'bar' );
        $viewParameterProvider2 = new ParameterProviderStub( $viewParameterProvider2Values );

        $containerMock = $this->getMock( 'Symfony\\Component\\DependencyInjection\\ContainerInterface' );
        $containerMock
            ->expects( $this->exactly( 3 ) )
            ->method( 'get' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'ezpublish.config.resolver', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->getMock( 'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface' ) ),
                        array( 'some_defined_service', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $viewParameterProvider1 ),
                        array( 'another_service', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $viewParameterProvider2 ),
                    )
                )
            );

        $listener = new ViewTemplateListener( $containerMock );
        $listener->onPreContentView( new PreContentViewEvent( $contentView ) );

        $expectedParams = array(
            'foo'           => 'bar',
            'some'          => 'thing',
            'osTypes'       => array( 'osx', 'linux', 'losedows' ),
            // $viewParameterProvider1
            'my_service'    => new ParameterWrapper( $viewParameterProvider1Values ),
            // $viewParameterProvider2
            'other_service' => new ParameterWrapper( $viewParameterProvider2Values )
        );
        $this->assertEquals( $expectedParams, $contentView->getParameters() );
    }
}
