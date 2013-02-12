<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\API\Repository\Tests\Stubs\Exceptions;
use eZ\Publish\Core\REST\Common;

class UnauthorizedExceptionTest extends ExceptionTest
{
    /**
     * Get expected status code
     *
     * @return int
     */
    protected function getExpectedStatusCode()
    {
        return 401;
    }

    /**
     * Get expected message
     *
     * @return string
     */
    protected function getExpectedMessage()
    {
        return "Unauthorized";
    }

    /**
     * Gets the exception
     *
     * @return \Exception
     */
    protected function getException()
    {
        return new Exceptions\UnauthorizedExceptionStub( "Test" );
    }

    /**
     * Gets the exception visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\UnauthorizedException
     */
    protected function getExceptionVisitor()
    {
        return new ValueObjectVisitor\UnauthorizedException(
            new Common\UrlHandler\eZPublish()
        );
    }
}
