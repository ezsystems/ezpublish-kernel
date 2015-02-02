<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search;

use eZ\Publish\SPI\FieldType\Indexable;

/**
 * Visits the criterion tree into a Solr query
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

