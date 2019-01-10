<?php

/**
 * File containing the RelationList FieldType class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RelationList;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\Core\FieldType\Value as BaseValue;

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
    const SELECTION_BROWSE = 0;
    /**
     * @todo following selection methods comes from legacy and may be interpreted as SELECTION_BROWSE by UI.
     * UI support will be evaluated on a case by case basis for future versions.
     */
    const SELECTION_DROPDOWN = 1;
    const SELECTION_LIST_WITH_RADIO_BUTTONS = 2;
    const SELECTION_LIST_WITH_CHECKBOXES = 3;
    const SELECTION_MULTIPLE_SELECTION_LIST = 4;
    const SELECTION_TEMPLATE_BASED_MULTI = 5;
    const SELECTION_TEMPLATE_BASED_SINGLE = 6;

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
    public function validateFieldSettings($fieldSettings)
    {
        $validationErrors = array();

        foreach ($fieldSettings as $name => $value) {
            if (!isset($this->settingsSchema[$name])) {
                $validationErrors[] = new ValidationError(
                    "Setting '%setting%' is unknown",
                    null,
                    array(
                        '%setting%' => $name,
                    ),
                    "[$name]"
                );
                continue;
            }

            switch ($name) {
                case 'selectionMethod':
                    if (!in_array($value, [
                        self::SELECTION_BROWSE,
                        self::SELECTION_DROPDOWN,
                        self::SELECTION_LIST_WITH_RADIO_BUTTONS,
                        self::SELECTION_LIST_WITH_CHECKBOXES,
                        self::SELECTION_MULTIPLE_SELECTION_LIST,
                        self::SELECTION_TEMPLATE_BASED_MULTI,
                        self::SELECTION_TEMPLATE_BASED_SINGLE,
                    ], true)) {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' must be one of %selection_browse%, %selection_dropdown%, %selection_list_with_radio_buttons%, %selection_list_with_checkboxes%, %selection_multiple_selection_list%, %selection_template_based_multi%, %selection_template_based_single%",
                            null,
                            array(
                                '%setting%' => $name,
                                '%selection_browse%' => self::SELECTION_BROWSE,
                                '%selection_dropdown%' => self::SELECTION_DROPDOWN,
                                '%selection_list_with_radio_buttons%' => self::SELECTION_LIST_WITH_RADIO_BUTTONS,
                                '%selection_list_with_checkboxes%' => self::SELECTION_LIST_WITH_CHECKBOXES,
                                '%selection_multiple_selection_list%' => self::SELECTION_MULTIPLE_SELECTION_LIST,
                                '%selection_template_based_multi%' => self::SELECTION_TEMPLATE_BASED_MULTI,
                                '%selection_template_based_single%' => self::SELECTION_TEMPLATE_BASED_SINGLE,
                            ),
                            "[$name]"
                        );
                    }
                    break;
                case 'selectionDefaultLocation':
                    if (!is_int($value) && !is_string($value) && $value !== null) {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be of either null, string or integer",
                            null,
                            array(
                                '%setting%' => $name,
                            ),
                            "[$name]"
                        );
                    }
                    break;
                case 'selectionContentTypes':
                    if (!is_array($value)) {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be of array type",
                            null,
                            array(
                                '%setting%' => $name,
                            ),
                            "[$name]"
                        );
                    }
                    break;
            }
        }

        return $validationErrors;
    }

    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezobjectrelationlist';
    }

    /**
     * Returns the name of the given field value.
     *
     * It will be used to generate content name and url alias if current field is designated
     * to be used in the content name/urlAlias pattern.
     *
     * @param \eZ\Publish\Core\FieldType\RelationList\Value $value
     *
     * @return string
     */
    public function getName(SPIValue $value)
    {
        throw new \RuntimeException('Name generation provided via NameableField set via "ezpublish.fieldType.nameable" service tag');
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
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param int|string|array|\eZ\Publish\API\Repository\Values\Content\ContentInfo|\eZ\Publish\Core\FieldType\RelationList\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\RelationList\Value The potentially converted and structurally plausible value.
     */
    protected function createValueFromInput($inputValue)
    {
        // ContentInfo
        if ($inputValue instanceof ContentInfo) {
            $inputValue = new Value(array($inputValue->id));
        } elseif (is_int($inputValue) || is_string($inputValue)) {
            // content id
            $inputValue = new Value(array($inputValue));
        } elseif (is_array($inputValue)) {
            // content id's
            $inputValue = new Value($inputValue);
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \eZ\Publish\Core\FieldType\RelationList\Value $value
     */
    protected function checkValueStructure(BaseValue $value)
    {
        if (!is_array($value->destinationContentIds)) {
            throw new InvalidArgumentType(
                '$value->destinationContentIds',
                'array',
                $value->destinationContentIds
            );
        }

        foreach ($value->destinationContentIds as $key => $destinationContentId) {
            if (!is_int($destinationContentId) && !is_string($destinationContentId)) {
                throw new InvalidArgumentType(
                    "\$value->destinationContentIds[$key]",
                    'string|int',
                    $destinationContentId
                );
            }
        }
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     * For this FieldType, the related object's name is returned.
     *
     * @param \eZ\Publish\Core\FieldType\RelationList\Value $value
     *
     * @return array
     */
    protected function getSortInfo(BaseValue $value)
    {
        return false;
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\RelationList\Value $value
     */
    public function fromHash($hash)
    {
        return new Value($hash['destinationContentIds']);
    }

    /**
     * Converts a $Value to a hash.
     *
     * @param \eZ\Publish\Core\FieldType\RelationList\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value)
    {
        return array('destinationContentIds' => $value->destinationContentIds);
    }

    /**
     * Returns whether the field type is searchable.
     *
     * @return bool
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
     * @param \eZ\Publish\Core\FieldType\RelationList\Value $value
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
    public function getRelations(SPIValue $value)
    {
        /* @var \eZ\Publish\Core\FieldType\RelationList\Value $value */
        return array(
            Relation::FIELD => $value->destinationContentIds,
        );
    }
}
