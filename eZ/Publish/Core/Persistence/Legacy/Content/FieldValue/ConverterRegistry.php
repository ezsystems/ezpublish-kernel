<?php
/**
 * File containing the FieldValue Converter Registry class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound;

class ConverterRegistry
{
    /**
     * Map of converters.
     *
     * @var array
     */
    protected $converterMap = array();

    /**
     * Create converter registry with converter map
     *
     * In $converterMap a array consists of a mapping of field
     * type names to object / callable is expected, in case of callable
     * factory converter object should be returned on execution. The object
     * is used to convert content fields and content type field definitions
     * to the legacy storage engine. The given class names must derive the
     * {@link \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter}
     * interface.
     *
     * @param array $converterMap A map where key is field type name, and value
     *              is a callable factory to get Converter OR Converter object
     */
    public function __construct( array $converterMap )
    {
        $this->converterMap = $converterMap;
    }

    /**
     * Register a $converter for $typeName
     *
     * @param string $typeName
     * @param mixed $converter Callable or converter instance
     *
     * @return void
     */
    public function register( $typeName, $converter )
    {
        $this->converterMap[$typeName] = $converter;
    }

    /**
     * Returns converter for $typeName
     *
     * @param string $typeName
     *
     * @throws \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound
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

            if ( !$this->converterMap[$typeName] instanceof Converter )
            {
                throw new \RuntimeException(
                    "Converter '$typeName' callable did not return a converter, instead: "
                    . gettype( $this->converterMap[$typeName] )
                );
            }
        }
        return $this->converterMap[$typeName];
    }
}
