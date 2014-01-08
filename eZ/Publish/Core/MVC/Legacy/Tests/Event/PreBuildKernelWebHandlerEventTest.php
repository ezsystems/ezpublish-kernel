<?php
/**
 * File containing the PreBuildKernelWebHandlerEventTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Tests\Event;

use eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelWebHandlerEvent;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class PreBuildKernelWebHandlerEventTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $parameterBag = new ParameterBag();
        $request = new Request();
        $event = new PreBuildKernelWebHandlerEvent( $parameterBag, $request );
        $this->assertSame( $parameterBag, $event->getParameters() );
        $this->assertSame( $request, $event->getRequest() );
    }
}
