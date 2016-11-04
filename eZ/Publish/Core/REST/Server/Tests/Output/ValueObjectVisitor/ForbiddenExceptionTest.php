<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Exceptions;

class ForbiddenExceptionTest extends ExceptionTest
{
    /**
     * Get expected status code.
     *
     * @return int
     */
    protected function getExpectedStatusCode()
    {
        return 403;
    }

    /**
     * Get expected message.
     *
     * @return string
     */
    protected function getExpectedMessage()
    {
        return 'Forbidden';
    }

    /**
     * Gets the exception.
     *
     * @return \Exception
     */
    protected function getException()
    {
        return new Exceptions\ForbiddenException('Test');
    }

    /**
     * Gets the exception visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\ForbiddenException
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\ForbiddenException();
    }
}
