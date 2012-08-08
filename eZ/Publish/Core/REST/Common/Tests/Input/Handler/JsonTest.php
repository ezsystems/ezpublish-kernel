<?php
/**
 * File containing the JsonTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\Input\Handler;

use eZ\Publish\Core\REST\Common;

/**
 * Json input handler test
 */
class JsonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests conversion of array to JSON
     */
    public function testConvertJson()
    {
        $handler = new Common\Input\Handler\Json();

        $this->assertSame(
            array(
                'text' => 'Hello world!',
            ),
            $handler->convert( '{"text":"Hello world!"}' )
        );
    }
}
