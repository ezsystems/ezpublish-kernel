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
    eZ\Publish\Core\Repository\Values\Content\ContentInfo,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;

/**
 * The Relation field type.
 *
 * This field type represents a relation to a content.
 *
 * hash format ({@see fromhash()}, {¨@see toHash()}):
 * array( 'destinationContentId' => (int)$destinationContentId );
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
     * Build a FiledType\Value object with the provided $destinationContentInfo as value.
     *
     * @param \eZ\Publish\Core\Repository\Values\Content\ContentInfo $destinationContentInfo
     * @return \eZ\Publish\Core\Repository\FieldType\Relation\Value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function buildValue( $destinationContentInfo )
    {
        if ( !$destinationContentInfo instanceof ContentInfo )
            throw new InvalidArgumentType(
                '$destinationContentInfo',
                'eZ\\Publish\\Core\\Repository\\Values\\Content\\ContentInfo',
                $destinationContentInfo
            );

        return new Value( $destinationContentInfo );
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
     * @return \eZ\Publish\Core\Repository\FieldType\Relation\Value
     */
    public function getDefaultDefaultValue()
    {
        return new Value();
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
                'eZ\\Publish\\Core\\Repository\\FieldType\\Relation\\Value',
                $inputValue
            );
        }

        if ( !$inputValue->destinationContent instanceof ContentInfo ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->destinationContent',
                '\eZ\Publish\Core\Repository\Values\Content\ContentInfo',
                $inputValue->destinationContent
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
        return (string)$value;
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\Repository\FieldType\Relation\Value $value
     */
    public function fromHash( $hash )
    {
        return new Value( $hash['destinationContentId'] );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\Repository\FieldType\Relation\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        return array( 'destinationContentId' => $value->destinationContent->contentId );
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
     * Converts a $value to a persistence value
     *
     * @param mixed $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue( $value )
    {
        // @todo Evaluate if creating the sortKey in every case is really needed
        //       Couldn't this be retrieved with a method, which would initialize
        //       that info on request only?
        return new FieldValue(
            array(
                "data" => $this->toHash( $value ),
                "externalData" => $this->toHash( $value ),
                "sortKey" => null,
            )
        );
    }

    /**
     * @see \eZ\Publish\Core\FieldType
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return mixed
     */
    public function fromPersistenceValue( FieldValue $fieldValue )
    {
        return $this->fromHash( $fieldValue->data );
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
