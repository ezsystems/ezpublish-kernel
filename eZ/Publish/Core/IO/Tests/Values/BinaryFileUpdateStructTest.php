<?php
/**
 * File containing the BinaryFileUpdateStructTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Tests\Values;

use eZ\Publish\Core\IO\Values\BinaryFileUpdateStruct;

class BinaryFileUpdateStructTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInputStream()
    {
        $resource = fopen( __FILE__, 'r' );
        $struct = new BinaryFileUpdateStruct();
        $struct->setInputStream( $resource );
        self::assertSame(
            $resource,
            $struct->getInputStream()
        );
    }
}
