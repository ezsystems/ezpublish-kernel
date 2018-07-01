<?php

/**
 * File containing the InvalidArgumentException ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

/**
 * InvalidArgumentException value object visitor.
 */
class InvalidArgumentException extends Exception
{
    /**
     * Returns HTTP status code.
     *
     * @return int
     */
    protected function getStatus()
    {
        return 406;
    }
}
