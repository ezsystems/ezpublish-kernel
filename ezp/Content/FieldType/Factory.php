<?php
/**
 * File containing the FieldType Factory class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType;
use ezp\Base\Configuration,
    ezp\Base\Exception\MissingClass;

/**
 * FieldType Factory class
 */
abstract class Factory
{
    /**
     * Factory method for building field type object based on $identifier.
     *
     * @throws \ezp\Base\Exception\MissingClass
     * @param string $identifier
     * @return \ezp\Content\FieldType
     */
    public static function build( $identifier )
    {
        $fieldTypeMap = Configuration::getInstance( "content" )->get( "fields", "Type" );
        if ( !isset( $fieldTypeMap[$identifier] ) )
        {
            throw new MissingClass( $identifier, "FieldType" );
        }
        return new $fieldTypeMap[$identifier];
    }
}
