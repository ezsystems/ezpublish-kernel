<?php
/**
 * File containing the ValueObjectAdapter class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating\Adapter;

use eZ\Publish\Core\MVC\Legacy\Templating\LegacyCompatible;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Generic adapter allowing any ValueObject implementation to be LegacyCompatible with the help of a hash map,
 * mapping the legacy attributes name to the value object property name (e.g. my_legacy_attribute_name => newPropertyName)
 */
class ValueObjectAdapter implements LegacyCompatible
{
    /**
     * @var \eZ\Publish\API\Repository\Values\ValueObject
     */
    private $valueObject;

    /**
     * Hash mapping legacy attribute name (key) to the embedded value object property (value)
     *
     * @var array
     */
    private $attributesMap;

    /**
     * @param \eZ\Publish\API\Repository\Values\ValueObject $valueObject The value object to decorate
     * @param array $attributesMap Hash mapping legacy attribute name (key) to the embedded value object property name (value)
     *                             Value can also be a closure which would be called directly with the value object as only parameter.
     */
    public function __construct( ValueObject $valueObject, array $attributesMap )
    {
        $this->valueObject = $valueObject;
        $this->attributesMap = $attributesMap;
    }

    /**
     * Returns true if object supports attribute $name
     *
     * @param string $name
     *
     * @return boolean
     */
    public function hasAttribute( $name )
    {
        return
            isset( $this->attributesMap[$name] )
            && (
                $this->attributesMap[$name] instanceof \Closure
                || isset( $this->valueObject->{$this->attributesMap[$name]} )
            );
    }

    /**
     * Returns the value of attribute $name.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException If $name is not supported as a valid attribute
     *
     * @return mixed
     */
    public function attribute( $name )
    {
        if ( !$this->hasAttribute( $name ) )
            return;

        // If $name corresponds to a closure, just execute it passing it current value object as argument.
        if ( $this->attributesMap[$name] instanceof \Closure )
        {
            return $this->attributesMap[$name]( $this->valueObject );
        }

        return $this->valueObject->{$this->attributesMap[$name]};
    }

    /**
     * Returns an array of supported attributes (only their names).
     *
     * @return array
     */
    public function attributes()
    {
        return array_keys( $this->attributesMap );
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    public function getValueObject()
    {
        return $this->valueObject;
    }
}
