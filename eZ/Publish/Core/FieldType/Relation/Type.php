<?php
/**
 * File containing the Relation FieldType class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\FieldType\Relation;
use eZ\Publish\Core\FieldType\FieldType,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;

/**
 * The Relation field type.
 *
 * This field type represents a simple string.
 */
class Type extends FieldType
{
    const SELECTION_BROWSE = 1;
    const SELECTION_DROPDOWN = 2;

    protected $allowedSettings = array(
        'selection_method' => self::SELECTION_BROWSE, // browse,
        'default_selection' => false,
    );

    /**
     * Build a Value object of current FieldType
     *
     * Build a FiledType\Value object with the provided $text as value.
     *
     * @param mixed $contentId
     * @return \eZ\Publish\Core\Repository\FieldType\TextLine\Value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function buildValue( /*$fromContentId, $toContentId*/ )
    {
        throw new \Exception( "Not implemented yet " );
        // return new Value( $text );
    }

    /**
     * Return the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "ezobjectrelation";
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\Repository\FieldType\TextLine\Value
     */
    public function getDefaultDefaultValue()
    {
        return new Value( '' );
    }

    /**
     * Checks the type and structure of the $Value.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param \eZ\Publish\Core\Repository\FieldType\TextLine\Value $inputValue
     *
     * @return \eZ\Publish\Core\Repository\FieldType\TextLine\Value
     */
    public function acceptValue( $inputValue )
    {
        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\Repository\\FieldType\\TextLine\\Value',
                $inputValue
            );
        }

        if ( !is_string( $inputValue->text ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->text',
                'string',
                $inputValue->text
            );
        }

        return $inputValue;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @todo String normalization should occur here.
     * @return array
     */
    protected function getSortInfo( $value )
    {
        return $value->text;
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\Repository\FieldType\TextLine\Value $value
     */
    public function fromHash( $hash )
    {
        return new Value( $hash );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\Repository\FieldType\TextLine\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        return $value->text;
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

    /**
     * Events handler (prePublish, postPublish, preCreate, postCreate)
     *
     * @param string $event - prePublish, postPublish, preCreate, postCreate
     * @param InternalRepository $repository
     * @param $fieldDef - the field definition of the field
     * @param $field - the field for which an action is performed
     */
    public function handleEvent( $event, /*InternalRepository*/ $repository, VersionInfo $versionInfo, FieldDefinition $fieldDef, Field  $field )
    {

    }
}
