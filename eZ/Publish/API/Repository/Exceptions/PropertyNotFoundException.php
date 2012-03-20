<?php
namespace eZ\Publish\API\Repository\Exceptions;

use RuntimeException,
    Exception;

/**
 * This Exception is thrown if an accessed property in a value object was not found
 * 
 * @package eZ\Publish\API\Repository\Exceptions
 *
 */
class PropertyNotFoundException extends RuntimeException
{
    /**
     * Generates: Property '{$propertyName}' not found
     *
     * @param string $propertyName
     * @param string|null $className Optionally to specify class in abstract/parent classes
     * @param \Exception|null $previous
     */
    public function __construct( $propertyName, $className = null, Exception $previous = null )
    {
        if ( $className === null )
            parent::__construct( "Property '{$propertyName}' not found", 0, $previous );
        else
            parent::__construct( "Property '{$propertyName}' not found on class '{$className}'", 0, $previous );
    }
}
