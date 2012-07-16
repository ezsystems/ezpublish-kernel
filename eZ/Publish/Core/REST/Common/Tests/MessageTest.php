<?php
/**
 * File containing the ContentTypeServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests;

use eZ\Publish\Core\REST\Common;

/**
 * Test case for operations in the ContentTypeService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\ContentTypeService
 * @group integration
 */
class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateMessageDefaultHeaders()
    {
        $message = new Common\Message();

        $this->assertSame( array(), $message->headers );
    }

    public function testCreateMessageDefaultBody()
    {
        $message = new Common\Message();

        $this->assertSame( '', $message->body );
    }

    public function testCreateMessageConstructorHeaders()
    {
        $message = new Common\Message(
            $headers = array(
                'Content-Type' => 'text/xml',
            )
        );

        $this->assertSame( $headers, $message->headers );
    }

    public function testCreateMessageConstructorBody()
    {
        $message = new Common\Message(
            array(),
            'Hello world!'
        );

        $this->assertSame( 'Hello world!', $message->body );
    }
}

