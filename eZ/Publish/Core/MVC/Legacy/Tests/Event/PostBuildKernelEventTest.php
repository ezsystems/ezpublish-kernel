<?php
/**
 * File containing the PostBuildKernelEventTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
