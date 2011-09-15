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
     * @param string $fieldTypeString
     * @return \ezp\Content\FieldType
     */
    public static function build( $fieldTypeString )
    {
        $fieldTypeMap = Configuration::getInstance( "content" )->get( "fields", "Type" );
        if ( !isset( $fieldTypeMap[$fieldTypeString] ) )
        {
            throw new MissingClass( $fieldTypeString, "FieldType" );
        }

        $fieldTypeClass = "{$fieldTypeMap[$fieldTypeString]}\\Type";
        if ( class_exists( $fieldTypeClass ) )
        {
            return new $fieldTypeClass;
        }

        throw new MissingClass( $fieldTypeString, "FieldType" );
    }

    /**
     * Builds a field value object for a field type, identified by $fieldTypeString, based on $stringValue.
     *
     * @param string $fieldTypeString
     * @param string $stringValue
     * @return \ezp\Content\FieldType\Value
     */
    public static function buildValueFromString( $fieldTypeString, $stringValue )
    {
        $fieldTypeMap = Configuration::getInstance( "content" )->get( "fields", "Type" );
        if ( !isset( $fieldTypeMap[$fieldTypeString] ) )
        {
            throw new MissingClass( $fieldTypeString, "FieldType" );
        }
        $fieldValueClass = "$fieldTypeMap[$fieldTypeString]\\Value";
        return $fieldValueClass::fromString( $stringValue );
    }
}
