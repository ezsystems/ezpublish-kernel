<?php
/**
 * File containing the PostBuildKernelEventTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Tests\Event;

use eZ\Publish\Core\MVC\Legacy\Event\PostBuildKernelEvent;
use PHPUnit_Framework_TestCase;

class PostBuildKernelEventTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $kernelHandler = $this->getMock( 'ezpKernelHandler' );
        $legacyKernel = $this
            ->getMockBuilder( 'eZ\Publish\Core\MVC\Legacy\Kernel' )
            ->setConstructorArgs( array( $kernelHandler, 'foo', 'bar' ) )
            ->getMock();
        $event = new PostBuildKernelEvent( $legacyKernel, $kernelHandler );
        $this->assertSame( $legacyKernel, $event->getLegacyKernel() );
        $this->assertSame( $kernelHandler, $event->getKernelHandler() );
    }
}
