<?php

/**
 * File containing the FieldValue Converter NotFound Exception class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;

/**
 * Exception thrown if no converter for a type was found.
 */
class NotFound extends NotFoundException
{
    /**
     * Creates a new exception for $typeName.
     *
     * @param mixed $typeName
     */
    public function __construct($typeName)
    {
        parent::__construct(
            'eZ\\Publish\\SPI\\Persistence\\Content\\FieldValue\\Converter\\*',
            $typeName
        );
    }
}
