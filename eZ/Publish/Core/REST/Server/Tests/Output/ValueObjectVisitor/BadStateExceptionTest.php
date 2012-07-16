<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Tests\Output\ValueObjectVisitor;
use eZ\Publish\API\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\API\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\API\Repository\Tests\Stubs\Exceptions;
use eZ\Publish\API\REST\Common;

class BadStateExceptionExceptionTest extends ExceptionTest
{
    /**
     * Get expected status code
     *
     * @return int
     */
    protected function getExpectedStatusCode()
    {
        return 409;
    }

    /**
     * Get expected message
     *
     * @return string
     */
    protected function getExpectedMessage()
    {
        return "Conflict";
    }

    /**
     * @return \Exception
     */
    protected function getException()
    {
        return new Exceptions\BadStateExceptionStub( "Test" );
    }

    /**
     * @return \eZ\Publish\API\REST\Server\Output\ValueObjectVisitor\Exception
     */
    protected function getExceptionVisitor()
    {
        return new ValueObjectVisitor\BadStateException(
            new Common\UrlHandler\eZPublish()
        );
    }
}
