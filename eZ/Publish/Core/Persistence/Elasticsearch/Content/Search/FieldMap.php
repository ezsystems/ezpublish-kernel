<?php
/**
 * File containing the Elasticsearch FieldMap class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search;

use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface;
use eZ\Publish\Core\Persistence\Solr\Content\Search\FieldRegistry;

/**
 * Provides field mapping information
 */
class FieldMap
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
    protected $nameGenerator;

    /**
     * Available field types
     *
     * @var array
     */
    protected $fieldTypes;

    /**
     * Create from content type handler and field registry
     *
     * @param \eZ\Publish\Core\Persistence\Solr\Content\Search\FieldRegistry $fieldRegistry
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldNameGenerator $nameGenerator
     */
    public function __construct(
        FieldRegistry $fieldRegistry,
        ContentTypeHandler $contentTypeHandler,
        FieldNameGenerator $nameGenerator
    )
    {
        $this->fieldRegistry = $fieldRegistry;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->nameGenerator = $nameGenerator;
    }

    /**
     * Get field type information
     *
     * Returns an array in the form:
     *
     * <code>
     *  array(
     *      "field-identifier" => array(
     *          "solr_field_name",
     *          …
     *      ),
     *      …
     *  )
     * </code>
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface $criterion
     *
     * @return array
     */
    public function getFieldTypes( CustomFieldInterface $criterion )
    {
        // @TODO: temp fixed by disabling caching, see https://jira.ez.no/browse/EZP-22834
        $this->fieldTypes = array();

        foreach ( $this->contentTypeHandler->loadAllGroups() as $group )
        {
            foreach ( $this->contentTypeHandler->loadContentTypes( $group->id ) as $contentType )
            {
                foreach ( $contentType->fieldDefinitions as $fieldDefinition )
                {
                    if ( !$fieldDefinition->isSearchable )
                    {
                        continue;
                    }

                    if ( $customField = $criterion->getCustomField( $contentType->identifier, $fieldDefinition->identifier ) )
                    {
                        $this->fieldTypes[$fieldDefinition->identifier]["custom"][] = $customField;
                        continue;
                    }

                    $fieldType = $this->fieldRegistry->getType( $fieldDefinition->fieldType );
                    foreach ( $fieldType->getIndexDefinition() as $name => $type )
                    {
                        $this->fieldTypes[$fieldDefinition->identifier][$type->type][] =
                            $this->nameGenerator->getTypedName(
                                $this->nameGenerator->getName( $name, $fieldDefinition->identifier, $contentType->identifier ),
                                $type
                            );
                    }
                }
            }
        }

        return $this->fieldTypes;
    }
}
