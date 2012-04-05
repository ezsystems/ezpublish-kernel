<?php

namespace eZ\Publish\API\REST\Client\Exceptions;

/**
 * This Exception is thrown on a write attempt in a read only property in a value object.
 * 
 * @package eZ\Publish\API\Repository\Exceptions
 *
 */
class PropertyReadOnlyException extends \eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException
{
    public function __construct( $propertyName )
    {
        parent::__construct(
            sprintf( 'Property "%s" is read-only.', $propertyName )
        );
    }
}


