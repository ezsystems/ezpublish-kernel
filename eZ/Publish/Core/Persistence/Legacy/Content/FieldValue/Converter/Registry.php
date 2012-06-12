<?php
/**
 * File containing the FieldValue Converter Registry class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound;

class Registry
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
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter $converter
     * @return void
     */
    public function register( $typeName, Converter $converter )
    {
        $this->converterMap[$typeName] = $converter;
    }

    /**
     * Returns converter for $typeName
     *
     * @param string $typeName
     * @throws eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter
     */
    public function getConverter( $typeName )
    {
        if ( !isset( $this->converterMap[$typeName] ) )
        {
            throw new NotFound( $typeName );
        }
        return $this->converterMap[$typeName];
    }
}
