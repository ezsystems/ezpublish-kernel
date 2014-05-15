<?php
/**
 * File containing the BinaryFileUpdateStructTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
