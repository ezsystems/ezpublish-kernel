<?php
/**
 * File containing the Elasticsearch Serializer class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldValueMapper;
use eZ\Publish\SPI\Persistence\Content\Search\FieldType\DocumentField;

/**
 *
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
     * @var \eZ\Publish\Core\Persistence\Solr\Content\Search\FieldNameGenerator
     */
    protected $nameGenerator;

    public function __construct(
        FieldValueMapper $fieldValueMapper,
        FieldNameGenerator $nameGenerator
    )
    {
        $this->fieldValueMapper = $fieldValueMapper;
        $this->nameGenerator = $nameGenerator;
    }

    /**
     * @param Document $document
     *
     * @return string
     */
    public function getIndexDocument( Document $document )
    {
        return json_encode( $this->getDocumentHash( $document ) );
    }

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
     *
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
