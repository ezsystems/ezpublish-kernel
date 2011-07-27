<?php
/**
 * File containing the FieldValueConverterRegistry class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\LegacyStorage\Content;
use ezp\Persistence\LegacyStorage\Exception\FieldValueConverterNotFoundException;

class FieldValueConverterRegistry
{
    /**
     * Map of converters.
     *
     * @var array
     */
    protected $converterMap = array();

    /**
     * Register $converter for $typeName
     *
     * @param string $typeName
     * @param FieldValueConverter $converter
     * @return void
     */
    public function register( $typeName, FieldValueConverter $converter )
    {
        $this->converterMap[$typeName] = $converter;
    }

    /**
     * Returns converter for $typeName
     *
     * @param string $typeName
     * @return FieldValueConverter
     */
    public function getConverter( $typeName )
    {
        if ( !isset( $this->converterMap[$typeName] ) )
        {
            throw new FieldValueConverterNotFoundException( $typeName );
        }
        return $this->converterMap[$typeName];
    }
}
