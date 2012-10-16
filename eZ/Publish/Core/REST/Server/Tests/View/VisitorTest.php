<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\View;
use eZ\Publish\Core\REST\Server\Tests\BaseTest;

use eZ\Publish\Core\REST\Server\View;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Common\Message;

use Qafoo\RMF;

class VisitorTest extends BaseTest
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $visitorMock;

    /**
     * testVisit
     */
    public function testVisit()
    {
        $viewVisitor = new View\Visitor( $this->getVisitorMock() );

        $request = new RMF\Request();
        $result  = new Values\SectionList( array(), '/content/sections' );

        $this->getVisitorMock()->expects( $this->once() )
            ->method( 'visit' )
            ->with( $result )
            ->will( $this->returnValue(
                new Message( array(), 'Foo Bar' )
        ) );

        ob_start();

        $viewVisitor->display( $request, $result );

        $this->assertEquals(
            'Foo Bar',
            ob_get_clean(),
            'Output not rendered correctly.'
        );
    }

    /**
     * @return \eZ\Publish\Core\REST\Common\Output\Visitor
     */
    protected function getVisitorMock()
    {
        if ( !isset( $this->visitorMock ) )
        {
            $this->visitorMock = $this->getMock(
                '\\eZ\\Publish\\Core\\REST\\Common\\Output\\Visitor',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->visitorMock;
    }
}
