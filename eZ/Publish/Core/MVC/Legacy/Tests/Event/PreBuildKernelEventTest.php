<?php
/**
 * File containing the PreBuildKernelEventTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
