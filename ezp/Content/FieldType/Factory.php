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
    ezp\Base\Exception\MissingClass,
    ezp\Persistence\Content\FieldValue;

/**
 * FieldType Factory class
 */
abstract class Factory
{
    /**
     * Factory method for building field type object based on $identifier.
     *
     * @throws \ezp\Base\Exception\MissingClass
     * @param string $fieldTypeIdentifier
     * @return \ezp\Content\FieldType
     */
    public static function build( $fieldTypeIdentifier )
    {
        $fieldTypeNS = self::getFieldTypeNamespace( $fieldTypeIdentifier );

        $fieldTypeClass = "$fieldTypeNS\\Type";
        if ( class_exists( $fieldTypeClass ) )
        {
            return new $fieldTypeClass;
        }

        throw new MissingClass( $fieldTypeIdentifier, "FieldType" );
    }

    /**
     * Builds a field value object for a field type from a $plainValue.
     * Field type is identified by $fieldTypeIdentifier (e.g. "ezstring").
     * Format for $plainValue is entirely up to the field type value object.
     *
     * @param string $fieldTypeIdentifier
     * @param mixed $plainValue
     * @return \ezp\Content\FieldType\Value
     */
    public static function buildValueFromPlain( $fieldTypeIdentifier, $plainValue )
    {
        $fieldValueClass = self::getFieldTypeNamespace( $fieldTypeIdentifier ) . "\\Value";
        return new $fieldValueClass( $plainValue );
    }

    /**
     * Returns field type namespace.
     * Field type is identified by $fiedTypeIdentifier.
     * Will throw a MissingClass exception if $fieldTypeIdentifier cannot be identified as a valid field type.
     * <code>
     * use ezp\Content\FieldType\Factory as FieldTypeFactory,
     *     ezp\Base\Exception\MissingClass;
     *
     * try
     * {
     *     // Will return "ezp\Content\FieldType\TextLine"
     *     $fieldTypeIdentifier = "ezstring";
     *     $fieldTypeNS = FieldTypeFactory::getFiedTypeNamespace( $fieldTypeIdentifier );
     * }
     * catch ( MissingClass $e )
     * {
     *     echo "Oops, seems that field type '$fieldTypeIdentifier' is invalid :-/";
     * }
     * </code>
     *
     * @param string $fieldTypeIdentifier Field type identifier
     * @return string
     * @throws \ezp\Base\Exception\MissingClass
     */
    public static function getFieldTypeNamespace( $fieldTypeIdentifier )
    {
        $fieldTypeMap = Configuration::getInstance( "content" )->get( "fields", "Type" );
        if ( !isset( $fieldTypeMap[$fieldTypeIdentifier] ) )
            throw new MissingClass( $fieldTypeIdentifier, "FieldType" );

        return $fieldTypeMap[$fieldTypeIdentifier];
    }
}
