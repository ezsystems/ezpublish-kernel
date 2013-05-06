<?php
/**
 * File containing the Page class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Page;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\Page\PageService;
use eZ\Publish\Core\FieldType\Page\HashConverter;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\SPI\Persistence\Content\FieldValue;

class Type extends FieldType
{
    /**
     * @var array
     */
    protected $settingsSchema = array(
        'defaultLayout' => array(
            'type' => 'string',
            'default' => '',
        )
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
    public function __construct( PageService $pageService, HashConverter $hashConverter )
    {
        $this->pageService = $pageService;
        $this->hashConverter = $hashConverter;
    }

    /**
     * Returns the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "ezpage";
    }

    /**
     * Validates the fieldSettings of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct
     *
     * @param mixed $fieldSettings
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateFieldSettings( $fieldSettings )
    {
        $validationErrors = array();

        foreach ( $fieldSettings as $name => $value )
        {
            if ( isset( $this->settingsSchema[$name] ) )
            {
                switch ( $name )
                {
                    case 'defaultLayout':
                        if ( !in_array( $value, $this->pageService->getAvailableZoneLayouts() ) )
                        {
                            $validationErrors[] = new ValidationError(
                                "Layout '{$value}' for setting '%setting%' is not available",
                                null,
                                array(
                                    'setting' => $name
                                )
                            );
                        }
                        break;
                }
            }
            else
            {
                $validationErrors[] = new ValidationError(
                    "Setting '%setting%' is unknown",
                    null,
                    array(
                        'setting' => $name
                    )
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
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return mixed
     */
    public function fromHash( $hash )
    {
        if ( $hash === null )
        {
            return null;
        }
        return $this->hashConverter->convertToValue( $hash );
    }

    /**
     * Converts a Value to a hash
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        if ( $this->isEmptyValue( $value ) )
        {
            return null;
        }
        return $this->hashConverter->convertFromValue( $value );
    }

    /**
     * Converts a persistence $fieldValue to a Value
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return mixed
     */
    public function fromPersistenceValue( FieldValue $fieldValue )
    {
        if ( $fieldValue->data === null )
        {
            return null;
        }

        return new Value( $fieldValue->data );
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
        if ( $value === null )
        {
            return new FieldValue(
                array(
                    "data" => null,
                    "externalData" => null,
                    "sortKey" => null
                )
            );
        }

        return new FieldValue(
            array(
                "data" => $value->page,
                "externalData" => null,
                "sortKey" => $this->getSortInfo( $value )
            )
        );
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * Return value is mixed. It should be something which is sensible for
     * sorting.
     *
     * It is up to the persistence implementation to handle those values.
     * Common string and integer values are safe.
     *
     * For the legacy storage it is up to the field converters to set this
     * value in either sort_key_string or sort_key_int.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function getSortInfo( $value )
    {
        return false;
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
        return '';
    }

    /**
     * Implements the core of {@see acceptValue()}.
     *
     * @param mixed $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\Page\Value The potentially converted and structurally plausible value.
     */
    protected function internalAcceptValue( $inputValue )
    {
        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\FieldType\\Page\\Value',
                $inputValue
            );
        }

        return $inputValue;
    }
}
