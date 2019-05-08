<?php

/**
 * File containing the Page class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Page;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\Core\FieldType\Page\Parts\Page;

class Type extends FieldType
{
    /**
     * @var array
     */
    protected $settingsSchema = array(
        'defaultLayout' => array(
            'type' => 'string',
            'default' => '',
        ),
    );

    /**
     * @var \eZ\Publish\Core\FieldType\Page\PageService
     */
    protected $pageService;

    /**
     * @var \eZ\Publish\Core\FieldType\Page\HashConverter
     */
    protected $hashConverter;

    /**
     * @param \eZ\Publish\Core\FieldType\Page\PageService $pageService
     * @param \eZ\Publish\Core\FieldType\Page\HashConverter $hashConverter
     */
    public function __construct(PageService $pageService, HashConverter $hashConverter)
    {
        $this->pageService = $pageService;
        $this->hashConverter = $hashConverter;
    }

    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezpage';
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
                    case 'defaultLayout':
                        if ($value !== '' && !in_array($value, $this->pageService->getAvailableZoneLayouts())) {
                            $validationErrors[] = new ValidationError(
                                "Layout '{$value}' for setting '%setting%' is not available",
                                null,
                                array(
                                    '%setting%' => $name,
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
                        '%setting%' => $name,
                    ),
                    "[$name]"
                );
            }
        }

        return $validationErrors;
    }

    /**
     * Returns the empty value for this field type.
     *
     * This value will be used, if no value was provided for a field of this
     * type and no default value was specified in the field definition.
     *
     * @return mixed
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Returns if the given $value is considered empty by the field type.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Value $value
     *
     * @return bool
     */
    public function isEmptyValue(SPIValue $value)
    {
        if ($value === null || $value == $this->getEmptyValue()) {
            return true;
        }

        foreach ($value->page->zones as $zone) {
            if (!empty($zone->blocks)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\Page\Value
     */
    public function fromHash($hash)
    {
        if ($hash === null) {
            return $this->getEmptyValue();
        }

        return $this->hashConverter->convertToValue($hash);
    }

    /**
     * Converts a Value to a hash.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value)
    {
        if ($this->isEmptyValue($value)) {
            return null;
        }

        return $this->hashConverter->convertFromValue($value);
    }

    /**
     * Converts a persistence $fieldValue to a Value.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return \eZ\Publish\Core\FieldType\Page\Value
     */
    public function fromPersistenceValue(FieldValue $fieldValue)
    {
        if ($fieldValue->data === null) {
            return $this->getEmptyValue();
        }

        return new Value($fieldValue->data);
    }

    /**
     * Converts a $value to a persistence value.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Value $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue(SPIValue $value)
    {
        return new FieldValue(
            array(
                'data' => $value->page,
                'externalData' => null,
                'sortKey' => $this->getSortInfo($value),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getSortInfo(BaseValue $value)
    {
        return false;
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\Page\Value The potentially converted and structurally plausible value.
     */
    protected function createValueFromInput($inputValue)
    {
        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Value $value
     */
    protected function checkValueStructure(BaseValue $value)
    {
        if (!$value->page instanceof Page) {
            throw new InvalidArgumentType(
                '$value->page',
                'eZ\\Publish\\Core\\FieldType\\Page\\Parts\\Page',
                $value->page
            );
        }
    }
}
