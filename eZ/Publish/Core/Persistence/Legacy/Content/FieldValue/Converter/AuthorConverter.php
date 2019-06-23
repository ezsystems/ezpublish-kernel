<?php

/**
 * File containing the Author converter.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\FieldType\Author\Type as AuthorType;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use DOMDocument;

class AuthorConverter implements Converter
{
    /**
     * Factory for current class.
     *
     * Note: Class should instead be configured as service if it gains dependencies.
     *
     * @deprecated since 6.8, will be removed in 7.x, use default constructor instead.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\AuthorConverter
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Converts data from $value to $storageFieldValue.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue)
    {
        $storageFieldValue->dataText = $this->generateXmlString($value->data);
        $storageFieldValue->sortKeyString = $value->sortKey;
    }

    /**
     * Converts data from $value to $fieldValue.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue)
    {
        $fieldValue->data = $this->restoreValueFromXmlString($value->dataText);
        $fieldValue->sortKey = $value->sortKeyString;
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef)
    {
        $storageDef->dataInt1 = (int)$fieldDef->fieldTypeConstraints->fieldSettings['defaultAuthor'];
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef)
    {
        $fieldDef->fieldTypeConstraints->fieldSettings = new FieldSettings(
            [
                'defaultAuthor' => $storageDef->dataInt1 ?? AuthorType::DEFAULT_VALUE_EMPTY,
            ]
        );

        $fieldDef->defaultValue->data = [];
    }

    /**
     * Returns the name of the index column in the attribute table.
     *
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     *
     * @return string
     */
    public function getIndexColumn()
    {
        return 'sort_key_string';
    }

    /**
     * Generates XML string from $authorValue to be stored in storage engine.
     *
     * @param array $authorValue
     *
     * @return string The generated XML string
     */
    private function generateXmlString(array $authorValue)
    {
        $doc = new DOMDocument('1.0', 'utf-8');

        $root = $doc->createElement('ezauthor');
        $doc->appendChild($root);

        $authors = $doc->createElement('authors');
        $root->appendChild($authors);

        foreach ($authorValue as $author) {
            $authorNode = $doc->createElement('author');
            $authorNode->setAttribute('id', $author['id']);
            $authorNode->setAttribute('name', $author['name']);
            $authorNode->setAttribute('email', $author['email']);
            $authors->appendChild($authorNode);
            unset($authorNode);
        }

        return $doc->saveXML();
    }

    /**
     * Restores an author Value object from $xmlString.
     *
     * @param string $xmlString XML String stored in storage engine
     *
     * @return \eZ\Publish\Core\FieldType\Author\Value[]
     */
    private function restoreValueFromXmlString($xmlString)
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $authors = [];

        if ($dom->loadXML($xmlString) === true) {
            foreach ($dom->getElementsByTagName('author') as $author) {
                $authors[] = [
                    'id' => $author->getAttribute('id'),
                    'name' => $author->getAttribute('name'),
                    'email' => $author->getAttribute('email'),
                ];
            }
        }

        return $authors;
    }
}
