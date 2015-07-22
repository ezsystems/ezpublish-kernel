<?php

/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Type class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\XmlText\Input\EzXml;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\API\Repository\Values\Content\Relation;
use DOMDocument;
use RuntimeException;

/**
 * XmlText field type.
 */
class Type extends FieldType
{
    /**
     * Default preset of tags available in online editor.
     */
    const TAG_PRESET_DEFAULT = 0;

    /**
     * Preset of tags for online editor intended for simple formatting options.
     */
    const TAG_PRESET_SIMPLE_FORMATTING = 1;

    /**
     * List of settings available for this FieldType.
     *
     * The key is the setting name, and the value is the default value for this setting
     *
     * @var array
     */
    protected $settingsSchema = array(
        'numRows' => array(
            'type' => 'int',
            'default' => 10,
        ),
        'tagPreset' => array(
            'type' => 'choice',
            'default' => self::TAG_PRESET_DEFAULT,
        ),
    );

    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezxmltext';
    }

    /**
     * Returns the name of the given field value.
     *
     * It will be used to generate content name and url alias if current field is designated
     * to be used in the content name/urlAlias pattern.
     *
     * @param \eZ\Publish\Core\FieldType\XmlText\Value $value
     *
     * @return string
     */
    public function getName(SPIValue $value)
    {
        $result = null;
        if ($section = $value->xml->documentElement->firstChild) {
            $textDom = $section->firstChild;

            if ($textDom && $textDom->hasChildNodes()) {
                $result = $textDom->firstChild->textContent;
            } elseif ($textDom) {
                $result = $textDom->textContent;
            }
        }

        if ($result === null) {
            $result = $value->xml->documentElement->textContent;
        }

        return trim($result);
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\XmlText\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Returns if the given $value is considered empty by the field type.
     *
     * @param \eZ\Publish\Core\FieldType\XmlText\Value $value
     *
     * @return bool
     */
    public function isEmptyValue(SPIValue $value)
    {
        if ($value->xml === null) {
            return true;
        }

        return !$value->xml->documentElement->hasChildNodes();
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param \eZ\Publish\Core\FieldType\XmlText\Value|\eZ\Publish\Core\FieldType\XmlText\Input|string $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\XmlText\Value The potentially converted and structurally plausible value.
     */
    protected function createValueFromInput($inputValue)
    {
        if (is_string($inputValue)) {
            if (empty($inputValue)) {
                $inputValue = Value::EMPTY_VALUE;
            }
            $inputValue = new EzXml($inputValue);
        }

        if ($inputValue instanceof Input) {
            $doc = new DOMDocument();
            $doc->loadXML($inputValue->getInternalRepresentation());
            $inputValue = new Value($doc);
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \eZ\Publish\Core\FieldType\XmlText\Value $value
     */
    protected function checkValueStructure(BaseValue $value)
    {
        if (!$value->xml instanceof DOMDocument) {
            throw new InvalidArgumentType(
                '$value->xml',
                'DOMDocument',
                $value
            );
        }
    }

    /**
     * Returns sortKey information.
     *
     * @see \eZ\Publish\Core\FieldType
     *
     * @param \eZ\Publish\Core\FieldType\XmlText\Value $value
     *
     * @return array|bool
     */
    protected function getSortInfo(BaseValue $value)
    {
        return false;
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     * $hash accepts the following keys:
     *  - xml (XML string which complies internal format).
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\XmlText\Value $value
     */
    public function fromHash($hash)
    {
        if (!isset($hash['xml'])) {
            throw new RuntimeException("'xml' index is missing in hash.");
        }

        $doc = new DOMDocument();
        $doc->loadXML($hash['xml']);

        return new Value($doc);
    }

    /**
     * Converts a $Value to a hash.
     *
     * @param \eZ\Publish\Core\FieldType\XmlText\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value)
    {
        return array('xml' => (string)$value);
    }

    /**
     * Creates a new Value object from persistence data.
     * $fieldValue->data is supposed to be a DOMDocument object.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return \eZ\Publish\Core\FieldType\XmlText\Value
     */
    public function fromPersistenceValue(FieldValue $fieldValue)
    {
        return new Value($fieldValue->data);
    }

    /**
     * @param \eZ\Publish\Core\FieldType\XmlText\Value $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue(SPIValue $value)
    {
        return new FieldValue(
            array(
                'data' => $value->xml,
                'externalData' => null,
                'sortKey' => $this->getSortInfo($value),
            )
        );
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
     * Validates the fieldSettings of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * @param mixed $fieldSettings
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateFieldSettings($fieldSettings)
    {
        $validationErrors = array();

        foreach ($fieldSettings as $name => $value) {
            if (isset($this->settingsSchema[$name])) {
                switch ($name) {
                    case 'numRows':
                        if (!is_integer($value)) {
                            $validationErrors[] = new ValidationError(
                                "Setting '%setting%' value must be of integer type",
                                null,
                                array(
                                    'setting' => $name,
                                ),
                                "[$name]"
                            );
                        }
                        break;
                    case 'tagPreset':
                        $definedTagPresets = array(
                            self::TAG_PRESET_DEFAULT,
                            self::TAG_PRESET_SIMPLE_FORMATTING,
                        );
                        if (!in_array($value, $definedTagPresets, true)) {
                            $validationErrors[] = new ValidationError(
                                "Setting '%setting%' is of unknown tag preset",
                                null,
                                array(
                                    'setting' => $name,
                                ),
                                "[$name]"
                            );
                        }
                        break;
                }
            } else {
                $validationErrors[] = new ValidationError(
                    "Setting '%setting%' is unknown",
                    null,
                    array(
                        'setting' => $name,
                    ),
                    "[$name]"
                );
            }
        }

        return $validationErrors;
    }

    /**
     * Returns relation data extracted from value.
     *
     * Not intended for \eZ\Publish\API\Repository\Values\Content\Relation::COMMON type relations,
     * there is a service API for handling those.
     *
     * @param \eZ\Publish\Core\FieldType\XmlText\Value $fieldValue
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
        $relations = array();

        /** @var \eZ\Publish\Core\FieldType\XmlText\Value $value */
        if ($value->xml instanceof DOMDocument) {
            $relations = array(
                Relation::LINK => $this->getRelatedObjectIds($value, Relation::LINK),
                Relation::EMBED => $this->getRelatedObjectIds($value, Relation::EMBED),
            );
        }

        return $relations;
    }

    protected function getRelatedObjectIds(Value $fieldValue, $relationType)
    {
        if ($relationType === Relation::EMBED) {
            $tagName = 'embed';
        } else {
            $tagName = 'link';
        }

        $locationIds = array();
        $contentIds = array();
        $linkTags = $fieldValue->xml->getElementsByTagName($tagName);
        if ($linkTags->length > 0) {
            /** @var $link \DOMElement */
            foreach ($linkTags as $link) {
                $contentId = $link->getAttribute('object_id');
                if (!empty($contentId)) {
                    $contentIds[] = $contentId;
                    continue;
                }
                $locationId = $link->getAttribute('node_id');
                if (!empty($locationId)) {
                    $locationIds[] = $locationId;
                }
            }
        }

        return array(
            'locationIds' => array_unique($locationIds),
            'contentIds' => array_unique($contentIds),
        );
    }
}
