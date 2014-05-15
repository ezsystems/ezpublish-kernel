<?php
/**
 * File containing a Io Handler test
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Tests\Handler;

use eZ\Publish\Core\IO\Handler\InMemory;
use eZ\Publish\Core\IO\Tests\Handler\Base as BaseHandlerTest;

/**
 * Handler test
 */
class InMemoryTest extends BaseHandlerTest
{
    /**
     * @return \eZ\Publish\SPI\IO\Handler
     */
    protected function getIOHandler()
    {
        return new InMemory();
    }
}
