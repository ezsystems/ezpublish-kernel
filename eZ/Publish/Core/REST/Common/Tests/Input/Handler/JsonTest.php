<?php
/**
 * File containing the JsonTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
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
        $handler = $this->getHandler();

        $this->assertSame(
            array(
                'text' => 'Hello world!',
            ),
            $handler->convert( '{"text":"Hello world!"}' )
        );
    }

    public function testConvertFieldValue()
    {
        $handler = $this->getHandler();

        $this->assertSame(
            array(
                'Field' => array(
                    'fieldValue' => array(
                        array(
                            'id' => 1,
                            'name' => 'Joe Sindelfingen',
                            'email' => 'sindelfingen@example.com',
                        ),
                        array(
                            'id' => 2,
                            'name' => 'Joe Bielefeld',
                            'email' => 'bielefeld@example.com',
                        ),
                    )
                )
            ),
            $handler->convert(
                '{"Field":{"fieldValue":[{"id":1,"name":"Joe Sindelfingen","email":"sindelfingen@example.com"},{"id":2,"name":"Joe Bielefeld","email":"bielefeld@example.com"}]}}'
            )
        );
    }

    protected function getHandler()
    {
        return new Common\Input\Handler\Json();
    }
}
