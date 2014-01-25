<?php
/**
 * File containing the SignalEventTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Event\Tests;

use eZ\Publish\Core\MVC\Symfony\Event\SignalEvent;
use PHPUnit_Framework_TestCase;

class SignalEventTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Event\SignalEvent::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\Event\SignalEvent::getSignal
     */
    public function testGetSignal()
    {
        $signal = $this->getMock( 'eZ\\Publish\\Core\\Repository\\SignalSlot\\Signal' );
        $event = new SignalEvent( $signal );
        $this->assertSame( $signal, $event->getSignal() );
    }
}
