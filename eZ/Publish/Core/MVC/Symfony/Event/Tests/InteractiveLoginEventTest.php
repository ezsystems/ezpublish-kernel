<?php
/**
 * File containing the InteractiveLoginEventTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Event\Tests;

use eZ\Publish\Core\MVC\Symfony\Event\InteractiveLoginEvent;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;

class InteractiveLoginEventTest extends PHPUnit_Framework_TestCase
{
    public function testGetSetAPIUser()
    {
        $event = new InteractiveLoginEvent( new Request(), $this->getMock( 'Symfony\Component\Security\Core\Authentication\Token\TokenInterface' ) );
        $this->assertFalse( $event->hasAPIUser() );
        $apiUser = $this->getMock( 'eZ\Publish\API\Repository\Values\User\User' );
        $event->setApiUser( $apiUser );
        $this->assertTrue( $event->hasAPIUser() );
        $this->assertSame( $apiUser, $event->getAPIUser() );
    }
}
