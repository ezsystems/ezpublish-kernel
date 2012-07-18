<?php
/**
 * File containing the FieldValue Converter Registry class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound;

class ConverterRegistry
{
    /**
     * Map of converters.
     *
     * @var array
     */
    protected $converterMap;

    /**
     * Create converter registry with converter map
     *
     * @param array $converterMap A map where key is field type key and value is a callable
     *                            factory to get Converter OR Converter instance
     */
    public function __construct( array $converterMap )
    {
        $this->converterMap = $converterMap;
    }

    /**
     * Returns converter for $typeName
     *
     * @param string $typeName
     *
     * @throws eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound
     * @throws \RuntimeException When type is neither Converter instance or callable factory
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter
     */
    public function getConverter( $typeName )
    {
        if ( !isset( $this->converterMap[$typeName] ) )
        {
            throw new NotFound( $typeName );
        }
        else if ( !$this->converterMap[$typeName] instanceof Converter )
        {
            if ( !is_callable( $this->converterMap[$typeName] ) )
            {
                throw new \RuntimeException( "Converter '$typeName' is neither callable or instance" );
            }

            $factory = $this->converterMap[$typeName];
            $this->converterMap[$typeName] = call_user_func( $factory );
        }
        return $this->converterMap[$typeName];
    }
}
