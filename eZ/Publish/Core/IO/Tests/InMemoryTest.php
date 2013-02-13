<?php
/**
 * File containing a Io Handler test
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Tests;

use eZ\Publish\Core\IO\InMemoryHandler as InMemory;
use eZ\Publish\Core\IO\Tests\Base as BaseHandlerTest;

/**
 * Handler test
 */
class InMemoryTest extends BaseHandlerTest
{
    /**
     * @return \eZ\Publish\SPI\IO\Handler
     */
    protected function getIoHandler()
    {
        return new InMemory();
    }
}
