<?php
/**
 * File containing the Elasticsearch Serializer class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search;

use eZ\Publish\SPI\Search\FieldType\DocumentField;

/**
 * Serializer serializes a Document to a string that can be passed
 * over Elasticsearch REST API.
 */
class Serializer
{
    /**
     * Field value mapper
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldValueMapper
     */
    protected $fieldValueMapper;

    /**
     * Field name generator
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldNameGenerator
     */
    protected $nameGenerator;

    /**
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldValueMapper $fieldValueMapper
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldNameGenerator $nameGenerator
     */
    public function __construct(
        FieldValueMapper $fieldValueMapper,
        FieldNameGenerator $nameGenerator
    )
    {
        $this->fieldValueMapper = $fieldValueMapper;
        $this->nameGenerator = $nameGenerator;
    }

    /**
     * Returns document _source that can be used for (bulk) indexing.
     *
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Document $document
     *
     * @return string
     */
    public function getIndexDocument( Document $document )
    {
        return json_encode( $this->getDocumentHash( $document ) );
    }

    /**
     * Returns bulk metadata for creating a new document or replacing an existing document.
     *
     * Note: _index parameter is omitted because it is configurable
     * on a gateway and passed as a part of the REST resource
     * in {@link \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Gateway::bulkIndex()}.
     *
     * @see \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Gateway::bulkIndex()
     *
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Document $document
     *
     * @return string
     */
    public function getIndexMetadata( Document $document )
    {
        $metadataHash = array(
            "index" => array(
                "_type" => $document->type,
                "_id" => $document->id,
            ),
        );

        return json_encode( $metadataHash );
    }

    /**
     * Converts given $document to a hash format that can be JSON encoded
     * to get a document _source.
     *
     * Implemented in a separate method because of a recursion needed to
     * handle nested documents.
     *
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Document $document
     *
     * @return array
     */
    protected function getDocumentHash( Document $document )
    {
        $hash = array();

        foreach ( $document->fields as $field )
        {
            if ( $field->type instanceof DocumentField )
            {
                $documents = $this->fieldValueMapper->map( $field );
                $values = array();

                foreach ( $documents as $document )
                {
                    $values[] = $this->getDocumentHash( $document );
                }
            }
            else
            {
                $values = (array)$this->fieldValueMapper->map( $field );
            }

            $name = $this->nameGenerator->getTypedName(
                $field->name,
                $field->type
            );

            if ( count( $values ) === 1 )
            {
                $hash[$name] = reset( $values );
            }
            else
            {
                $hash[$name] = $values;
            }
        }

        return $hash;
    }
}
