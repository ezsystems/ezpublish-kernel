<?php
/**
 * File containing the SortClauseVisitor\MapLocationDistance class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Persistence\Solr\Content\Search\FieldNameGenerator;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\Core\Persistence\Solr\Content\Search\FieldRegistry;
use RuntimeException;

/**
 * Visits the sortClause tree into a Elasticsearch query
 */
class MapLocationDistance extends SortClauseVisitor
{
    /**
     * Field registry
     *
     * @var \eZ\Publish\Core\Persistence\Solr\Content\Search\FieldRegistry
     */
    protected $fieldRegistry;

    /**
     * Content type handler
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * Field name generator
     *
     * @var \eZ\Publish\Core\Persistence\Solr\Content\Search\FieldNameGenerator
     */
    protected $fieldNameGenerator;

    /**
     * Name of the field type that sort clause can handle
     *
     * @var string
     */
    protected $typeName = "ez_geolocation";

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\Core\Persistence\Solr\Content\Search\FieldRegistry $fieldRegistry
     * @param \eZ\Publish\Core\Persistence\Solr\Content\Search\FieldNameGenerator $fieldNameGenerator
     */
    public function __construct(
        ContentTypeHandler $contentTypeHandler,
        FieldRegistry $fieldRegistry,
        FieldNameGenerator $fieldNameGenerator
    )
    {
        $this->contentTypeHandler = $contentTypeHandler;
        $this->fieldRegistry = $fieldRegistry;
        $this->fieldNameGenerator = $fieldNameGenerator;
    }

    /**
     * Get field type information
     *
     * TODO: extract/abstract FieldMap (and handle custom field?? TBD for sort)
     * TODO: as data is nested/namespaced there is no real need for type info in field name
     * TODO: ^^^ type identifier is not indexed so it MUST NOT be used as far as that is the case anyway
     *
     * @param string $contentTypeIdentifier
     * @param string $fieldDefinitionIdentifier
     * @param string $languageCode
     *
     * @return array
     */
    protected function getFieldTypes( $contentTypeIdentifier, $fieldDefinitionIdentifier, $languageCode )
    {
        $types = array();

        foreach ( $this->contentTypeHandler->loadAllGroups() as $group )
        {
            foreach ( $this->contentTypeHandler->loadContentTypes( $group->id ) as $contentType )
            {
                if ( $contentType->identifier !== $contentTypeIdentifier )
                {
                    continue;
                }

                foreach ( $contentType->fieldDefinitions as $fieldDefinition )
                {
                    if ( $fieldDefinition->identifier !== $fieldDefinitionIdentifier )
                    {
                        continue;
                    }

                    // TODO: find a better way to handle non-translatable fields?
                    if ( $languageCode === null || $fieldDefinition->isTranslatable )
                    {
                        $fieldType = $this->fieldRegistry->getType( $fieldDefinition->fieldType );

                        foreach ( $fieldType->getIndexDefinition() as $name => $type )
                        {
                            // TODO should probably better use $name as key
                            $types[$type->type] =
                                $this->fieldNameGenerator->getTypedName(
                                    $this->fieldNameGenerator->getName(
                                        $name,
                                        $fieldDefinition->identifier,
                                        $contentType->identifier
                                    ),
                                    $type
                                );
                        }
                    }

                    break 3;
                }
            }
        }

        return $types;
    }

    /**
     * CHeck if visitor is applicable to current sortClause
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return boolean
     */
    public function canVisit( SortClause $sortClause )
    {
        return $sortClause instanceof SortClause\MapLocationDistance;
    }

    /**
     * Map field value to a proper Elasticsearch representation
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no sortable fields are found for the given sort clause target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return string
     */
    public function visit( SortClause $sortClause )
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\MapLocationTarget $target */
        $target = $sortClause->targetData;
        $types = $this->getFieldTypes(
            $target->typeIdentifier,
            $target->fieldIdentifier,
            $target->languageCode
        );

        if ( empty( $types ) )
        {
            // TODO should this really crash? maybe a dedicated exception is needed
            throw new RuntimeException( "No sortable fields found" );
        }

        $fieldName = $types["ez_geolocation"];

        $sortClause = array(
            "_geo_distance" => array(
                "nested_path" => "fields_doc",
                "mode" => "max",
                "order" => $this->getDirection( $sortClause ),
                "fields_doc.{$fieldName}" => array(
                    "lat" => $target->latitude,
                    "lon" => $target->longitude,
                ),
                "unit" => "km",
            ),
        );

        // TODO should maybe somehow filter even when language filter is not used (non-translatable field)
        if ( $target->languageCode !== null )
        {
            $sortClause["_geo_distance"]["nested_filter"] = array(
                "term" => array(
                    // TODO: fix normalization
                    "fields_doc.meta_language_code_id" => str_replace( "-", "", $target->languageCode ),
                ),
            );
        }

        return $sortClause;
    }
}
