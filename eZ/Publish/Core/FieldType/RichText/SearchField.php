<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText;

use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\FieldType\Indexable;
use eZ\Publish\SPI\Search;
use DOMDocument;
use DOMNode;

/**
 * Indexable definition for RichText field type.
 *
 * @deprecated since 7.4, use \EzSystems\EzPlatformRichText\eZ\FieldType\RichText\SearchField from EzPlatformRichTextBundle.
 */
class SearchField implements Indexable
{
    /**
     * Get index data for field for search backend.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    public function getIndexData(Field $field, FieldDefinition $fieldDefinition)
    {
        $document = new DOMDocument();
        $document->loadXML($field->value->data);

        return [
            new Search\Field(
                'value',
                self::extractShortText($document),
                new Search\FieldType\StringField()
            ),
            new Search\Field(
                'fulltext',
                $this->extractText($document->documentElement),
                new Search\FieldType\FullTextField()
            ),
        ];
    }

    /**
     * Extracts text content of the given $node.
     *
     * @param \DOMNode $node
     *
     * @return string
     */
    private function extractText(DOMNode $node)
    {
        $text = '';

        if ($node->childNodes) {
            foreach ($node->childNodes as $child) {
                $text .= $this->extractText($child);
            }
        } else {
            $text .= $node->nodeValue . ' ';
        }

        return $text;
    }

    /**
     * Extracts short text content of the given $document.
     *
     * @internal Only for use by RichText FieldType itself.
     *
     * @param \DOMDocument $document
     *
     * @return string
     */
    public static function extractShortText(DOMDocument $document)
    {
        $result = null;
        // try to extract first paragraph/tag
        if ($section = $document->documentElement->firstChild) {
            $textDom = $section->firstChild;

            if ($textDom && $textDom->hasChildNodes()) {
                $result = $textDom->firstChild->textContent;
            } elseif ($textDom) {
                $result = $textDom->textContent;
            }
        }

        if ($result === null) {
            $result = $document->documentElement->textContent;
        }

        // In case of newlines, extract first line. Also limit size to 255 which is maxsize on sql impl.
        $lines = preg_split('/\r\n|\n|\r/', trim($result), -1, PREG_SPLIT_NO_EMPTY);

        return empty($lines) ? '' : trim(mb_substr($lines[0], 0, 255));
    }

    /**
     * Get index field types for search backend.
     *
     * @return \eZ\Publish\SPI\Search\FieldType[]
     */
    public function getIndexDefinition()
    {
        return [
            'value' => new Search\FieldType\StringField(),
        ];
    }

    /**
     * Get name of the default field to be used for matching.
     *
     * As field types can index multiple fields (see MapLocation field type's
     * implementation of this interface), this method is used to define default
     * field for matching. Default field is typically used by Field criterion.
     *
     * @return string
     */
    public function getDefaultMatchField()
    {
        return 'value';
    }

    /**
     * Get name of the default field to be used for sorting.
     *
     * As field types can index multiple fields (see MapLocation field type's
     * implementation of this interface), this method is used to define default
     * field for sorting. Default field is typically used by Field sort clause.
     *
     * @return string
     */
    public function getDefaultSortField()
    {
        return $this->getDefaultMatchField();
    }
}
