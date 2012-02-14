<?php

namespace eZ\Publish\API\Repository\Tests\Stubs\Exceptions;
use eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException;

/**
 * This Exception is thrown on a write attempt in a read only property in a value object.
 * 
 * @package eZ\Publish\API\Repository\Exceptions
 *
 */
class PropertyReadOnlyExceptionStub extends PropertyReadOnlyException
{
    public function __construct( $propertyName )
    {
        parent::__construct(
            sprintf( 'Property "%s" is read-only.', $propertyName )
        );
    }
}


