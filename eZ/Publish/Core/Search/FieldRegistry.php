<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search;

use eZ\Publish\SPI\FieldType\Indexable;

/**
 * Registry for field type's Indexable interface implementations available to Search Engines.
 */
class FieldRegistry
{
    /**
     * Registered field types
     *
     * @var array(string => Indexable)
     */
    protected $types = array();

    /**
     * COnstruct from optional Indexable type array
     *
     * @param array $types
     *
     * @return void
     */
    public function __construct( array $types = array() )
    {
        foreach ( $types as $name => $type )
        {
            $this->registerType( $name, $type );
        }
    }

    /**
     * Register another indexable type
     *
     * @param string $name
     * @param Indexable $type
     *
     * @return void
     */
    public function registerType( $name, Indexable $type )
    {
        $this->types[$name] = $type;
    }

    /**
     * Get indexable type
     *
     * @param string $name
     *
     * @return Indexable
     */
    public function getType( $name )
    {
        if ( !isset( $this->types[$name] ) )
        {
            throw new \OutOfBoundsException( "No type registered for $name." );
        }

        return $this->types[$name];
    }
}

