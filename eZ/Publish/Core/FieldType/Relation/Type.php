<?php
/**
 * File containing the Relation FieldType class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Relation;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * The Relation field type.
 *
 * This field type represents a relation to a content.
 *
 * hash format ({@see fromhash()}, {@see toHash()}):
 * array( 'destinationContentId' => (int)$destinationContentId );
 */
class Type extends FieldType
{
    const SELECTION_BROWSE = 0,
          SELECTION_DROPDOWN = 1;

    protected $settingsSchema = array(
        'selectionMethod' => array(
            'type' => 'int',
            'default' => self::SELECTION_BROWSE,
        ),
        'selectionRoot' => array(
            'type' => 'string',
            'default' => '',
        ),
    );

    /**
     * @see \eZ\Publish\Core\FieldType\FieldType::validateFieldSettings()
     *
     * @param mixed $fieldSettings
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateFieldSettings( $fieldSettings )
    {
        $validationResult = array();

        foreach ( array_keys( $fieldSettings ) as $setting )
        {
            if ( !in_array( $setting, array_keys( $this->settingsSchema ) ) )
            {
                $validationResult[] = new ValidationError(
                    "Unknown setting %setting%",
                    null,
                    array( 'setting' => $setting )
                );
            }
        }
        if ( !isset( $fieldSettings['selectionMethod'] ) ||
            ( $fieldSettings['selectionMethod'] != self::SELECTION_BROWSE && $fieldSettings['selectionMethod'] != self::SELECTION_DROPDOWN ) )
        {
            $validationResult[] = new ValidationError(
                "Setting selection method must be either %selection_browse% or %selection_dropdown%",
                null,
                array( 'selection_browse' => self::SELECTION_BROWSE, 'selection_dropdown' => self::SELECTION_DROPDOWN )
            );
        }

        if ( !isset( $fieldSettings['selectionRoot'] ) ||
            ( !is_string( $fieldSettings['selectionRoot'] ) && !is_numeric( $fieldSettings['selectionRoot'] ) ) )
        {
            $validationResult[] = new ValidationError(
                "Setting selection root must be either a string or numeric integer"
            );
        }

        return $validationResult;
    }

    /**
     * Returns the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "ezobjectrelation";
    }

    /**
     * Returns the name of the given field value.
     *
     * It will be used to generate content name and url alias if current field is designated
     * to be used in the content name/urlAlias pattern.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function getName( $value )
    {
        throw new \RuntimeException( '@todo Implement this method' );
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\Relation\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Checks the type and structure of the $Value.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param mixed $inputValue A ContentInfo or content ID to build from, or a Relation\Value
     *
     * @return \eZ\Publish\Core\FieldType\Relation\Value The potentially converted and structurally plausible value.
     */
    protected function internalAcceptValue( $inputValue )
    {
        // ContentInfo
        if ( $inputValue instanceof ContentInfo )
        {
            $inputValue = new Value( $inputValue->id );
        }
        // content id
        else if ( is_integer( $inputValue ) || is_string( $inputValue ) )
        {
            $inputValue = new Value( $inputValue );
        }
        else if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\Repository\\FieldType\\Relation\\Value',
                $inputValue
            );
        }

        if ( !is_integer( $inputValue->destinationContentId ) && !is_string( $inputValue->destinationContentId ) && $inputValue->destinationContentId !== null )
        {
            throw new InvalidArgumentType(
                '$inputValue->destinationContentId',
                'string|int',
                $inputValue->destinationContentId
            );
        }

        return $inputValue;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     * For this FieldType, the related object's name is returned.
     *
     * @todo Repository needs to be provided to be able to get Content Relation name(s), and it is in ctor
     *
     * @return array
     */
    protected function getSortInfo( $value )
    {
        return (string)$value;
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\Relation\Value $value
     */
    public function fromHash( $hash )
    {
        return new Value( $hash['destinationContentId'] );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\FieldType\Relation\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        return array( 'destinationContentId' => $value->destinationContentId );
    }

    /**
     * Returns whether the field type is searchable
     *
     * @return boolean
     */
    public function isSearchable()
    {
        return true;
    }

    /**
     * Returns relation data extracted from value.
     *
     * Not intended for \eZ\Publish\API\Repository\Values\Content\Relation::COMMON type relations,
     * there is an API for handling those.
     *
     * @param \eZ\Publish\Core\FieldType\Value $fieldValue
     *
     * @return array Hash with relation type as key and array of destination content ids as value.
     *
     * Example:
     * <code>
     *  array(
     *      \eZ\Publish\API\Repository\Values\Content\Relation::LINK => array(
     *          "contentIds" => array( 12, 13, 14 ),
     *          "locationIds" => array( 24 )
     *      ),
     *      \eZ\Publish\API\Repository\Values\Content\Relation::EMBED => array(
     *          "contentIds" => array( 12 ),
     *          "locationIds" => array( 24, 45 )
     *      ),
     *      \eZ\Publish\API\Repository\Values\Content\Relation::ATTRIBUTE => array( 12 )
     *  )
     * </code>
     */
    public function getRelations( BaseValue $fieldValue )
    {
        return array(
            Relation::FIELD => array( $fieldValue->destinationContentId )
        );
    }
}
