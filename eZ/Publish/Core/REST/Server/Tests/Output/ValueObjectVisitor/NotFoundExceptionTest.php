<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

class NotFoundExceptionTest extends ExceptionTest
{
    /**
     * Get expected status code.
     *
     * @return int
     */
    protected function getExpectedStatusCode()
    {
        return 404;
    }

    /**
     * Get expected message.
     *
     * @return string
     */
    protected function getExpectedMessage()
    {
        return 'Not Found';
    }

    /**
     * Get the exception.
     *
     * @return \Exception
     */
    protected function getException()
    {
        return $this->getMockForAbstractClass(NotFoundException::class);
    }

    /**
     * Get the exception visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\NotFoundException
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\NotFoundException();
    }
}
