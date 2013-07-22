<?php
/**
 * File containing the XmlText converter
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText\XsltConverter;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText\XsdValidator;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\Core\FieldType\XmlText\Value;
use DOMDocument;

class XmlText implements Converter
{
    /**
     * ezxml empty value, needed for conversion to Docbook
     */
    const EMPTY_VALUE = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<section xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"/>
EOT;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText\XsltConverter
     */
    protected $toStorageConverter;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText\XsltConverter
     */
    protected $fromStorageConverter;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText\XsdValidator
     */
    protected $ezxmlValidator;

    /**
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText\XsltConverter $toStorageConverter
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText\XsltConverter $fromStorageConverter
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\XmlText\XsdValidator $ezxmlValidator
     */
    public function __construct(
        XsltConverter $toStorageConverter,
        XsltConverter $fromStorageConverter,
        XsdValidator $ezxmlValidator
    )
    {
        $this->toStorageConverter = $toStorageConverter;
        $this->fromStorageConverter = $fromStorageConverter;
        $this->ezxmlValidator = $ezxmlValidator;
    }

    /**
     * Converts data from $value to $storageFieldValue
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
        $ezxml = $this->toStorageConverter->convert( $value->data );

        $document = new DOMDocument;
        $document->loadXML( $ezxml );
        $errors = $this->ezxmlValidator->validate( $document );

        if ( !empty( $errors ) )
        {
            throw new \RuntimeException( "Validation of XML content failed: " . join( "\n", $errors ) );
        }

        $storageFieldValue->dataText = $ezxml;
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $document = new DOMDocument;
        $document->loadXML( $value->dataText ?: static::EMPTY_VALUE );

        $errors = $this->ezxmlValidator->validate( $document );

        if ( !empty( $errors ) )
        {
            throw new \RuntimeException( "Validation of XML content failed: " . join( "\n", $errors ) );
        }

        $xmlString = $this->fromStorageConverter->convert( $document );
        $document->loadXML( $xmlString );

        $fieldValue->data = $document;
    }

    /**
     * Converts field definition data from $fieldDefinition into $storageFieldDefinition
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDefinition
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDefinition, StorageFieldDefinition $storageDefinition )
    {
        $storageDefinition->dataInt1 = $fieldDefinition->fieldTypeConstraints->fieldSettings['numRows'];
        $storageDefinition->dataText2 = $fieldDefinition->fieldTypeConstraints->fieldSettings['tagPreset'];
        if ( !empty( $fieldDefinition->defaultValue->data ) )
            $storageDefinition->dataText1 = $fieldDefinition->defaultValue->data->saveXML();
    }

    /**
     * Converts field definition data from $storageDefinition into $fieldDefinition
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDefinition
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDefinition, FieldDefinition $fieldDefinition )
    {
        $fieldDefinition->fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                'numRows' => $storageDefinition->dataInt1,
                'tagPreset' => $storageDefinition->dataText2
            )
        );

        $defaultValue = null;
        if ( !empty( $storageDefinition->dataText1 ) )
        {
            $defaultValue = new DOMDocument;
            $defaultValue->loadXML( $storageDefinition->dataText1 );
        }
        $fieldDefinition->defaultValue->data = $defaultValue;
    }

    /**
     * Returns the name of the index column in the attribute table
     *
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     *
     * @return string|false
     */
    public function getIndexColumn()
    {
        return false;
    }

}
