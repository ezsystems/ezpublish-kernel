<?php
/**
 * File containing the RelationList FieldType class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\RelationList;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;

/**
 * The RelationList field type.
 *
 * This field type represents a relation to a content.
 *
 * hash format ({@see fromhash()}, {@see toHash()}):
 * array( 'destinationContentIds' => array( (int)$destinationContentId ) );
 */
class Type extends FieldType
{
    /**
     * @todo Consider to add all 6 selection options
     *
     */
    const SELECTION_BROWSE = 0,
          SELECTION_DROPDOWN = 1;

    protected $settingsSchema = array(
        'selectionMethod' => array(
            'type' => 'int',
            'default' => self::SELECTION_BROWSE,
        ),
        'selectionDefaultLocation' => array(
            'type' => 'string',
            'default' => null,
        ),
        'selectionContentTypes' => array(
            'type' => 'array',
            'default' => array(),
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

        foreach( array_keys( $fieldSettings ) as $setting )
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

        if ( !isset( $fieldSettings['selectionMethod'] )
            || ( $fieldSettings['selectionMethod'] !== self::SELECTION_BROWSE && $fieldSettings['selectionMethod'] !== self::SELECTION_DROPDOWN ) )
        {
            $validationResult[] = new ValidationError(
                "Setting selection method must be either %selection_browse% or %selection_dropdown%",
                null,
                array( 'selection_browse' => self::SELECTION_BROWSE, 'selection_dropdown' => self::SELECTION_DROPDOWN )
            );
        }

        if ( !isset( $fieldSettings['selectionDefaultLocation'] )
            || ( !is_string( $fieldSettings['selectionDefaultLocation'] ) && !is_numeric( $fieldSettings['selectionDefaultLocation'] ) ) )
        {
            $validationResult[] = new ValidationError(
                "Setting selectionDefaultLocation must be either a string or numeric integer"
            );
        }

        if ( isset( $fieldSettings['selectionContentTypes'] ) && !is_array( $fieldSettings['selectionContentTypes'] ) )
        {
            $validationResult[] = new ValidationError(
                "Setting selectionContentTypes must be a array"
            );
        }

        return $validationResult;
    }

    /**
     * Return the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "ezobjectrelationlist";
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
     * @return \eZ\Publish\Core\FieldType\RelationList\Value
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
     * @param mixed $inputValue A ContentInfo, content ID or list of content ID's to build from, or a RelationList\Value
     *
     * @return \eZ\Publish\Core\FieldType\RelationList\Value The potentially converted and structurally plausible value.
     */
    protected function internalAcceptValue( $inputValue )
    {
        // ContentInfo
        if ( $inputValue instanceof ContentInfo )
        {
            $inputValue = new Value( array( $inputValue->id ) );
        }
        // content id
        elseif ( is_integer( $inputValue ) || is_string( $inputValue ) )
        {
            $inputValue = new Value( array( $inputValue ) );
        }
        // content id's
        elseif ( is_array( $inputValue ) )
        {
            $inputValue = new Value( $inputValue );
        }
        else if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\Repository\\FieldType\\RelationList\\Value',
                $inputValue
            );
        }

        foreach ( $inputValue->destinationContentIds as $key => $destinationContentId )
        {
            if ( !is_integer( $destinationContentId ) && !is_string( $destinationContentId ) )
            {
               throw new InvalidArgumentType(
                    "\$inputValue->destinationContentIds[$key]",
                    'string|int',
                   $destinationContentId
                );
            }
        }

        return $inputValue;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     * For this FieldType, the related object's name is returned.
     *
     * @todo What to do here?
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
     * @return \eZ\Publish\Core\FieldType\RelationList\Value $value
     */
    public function fromHash( $hash )
    {
        return new Value( $hash['destinationContentIds'] );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\FieldType\RelationList\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        return array( 'destinationContentIds' => $value->destinationContentIds );
    }

    /**
     * Returns whether the field type is searchable
     *
     * @return bool
     */
    public function isSearchable()
    {
        return true;
    }
}
