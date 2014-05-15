<?php
/**
 * File containing the PreBuildKernelEventTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Tests\Event;

use eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelEvent;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class PreBuildKernelEventTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $parameterBag = new ParameterBag();
        $event = new PreBuildKernelEvent( $parameterBag );
        $this->assertSame( $parameterBag, $event->getParameters() );
    }
}
