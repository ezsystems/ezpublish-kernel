<?php

/**
 * File containing the JsonTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Common\Tests\Input\Handler;

use eZ\Publish\Core\REST\Common;
use PHPUnit_Framework_TestCase;

/**
 * Json input handler test.
 */
class JsonTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     */
    public function testConvertInvalidJson()
    {
        $handler = $this->getHandler();
        $handler->convert('{text:"Hello world!"}');
    }

    /**
     * Tests conversion of array to JSON.
     */
    public function testConvertJson()
    {
        $handler = $this->getHandler();

        $this->assertSame(
            array(
                'text' => 'Hello world!',
            ),
            $handler->convert('{"text":"Hello world!"}')
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
                    ),
                ),
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
