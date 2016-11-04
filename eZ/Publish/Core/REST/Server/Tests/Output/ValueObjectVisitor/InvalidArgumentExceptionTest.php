<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Exceptions;

class InvalidArgumentExceptionTest extends ExceptionTest
{
    /**
     * Get expected status code.
     *
     * @return int
     */
    protected function getExpectedStatusCode()
    {
        return 406;
    }

    /**
     * Get expected message.
     *
     * @return string
     */
    protected function getExpectedMessage()
    {
        return 'Not Acceptable';
    }

    /**
     * Gets the exception.
     *
     * @return \Exception
     */
    protected function getException()
    {
        return new Exceptions\InvalidArgumentException('Test');
    }

    /**
     * Gets the exception visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\InvalidArgumentException
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\InvalidArgumentException();
    }
}
