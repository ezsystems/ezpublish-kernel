<?php
/**
 * File containing the PreBuildKernelWebHandlerEventTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
