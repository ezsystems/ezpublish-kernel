<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;

use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\Core\Persistence\Solr\Content\Search\FieldNameGenerator;
use eZ\Publish\Core\Persistence\Solr\Content\Search\FieldRegistry;

/**
 * Visits the Field criterion
 */
abstract class Field extends CriterionVisitor
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
     * @var FieldNameGenerator
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
     * @param FieldRegistry $fieldRegistry
     * @param ContentTypeHandler $contentTypeHandler
     *
     * @return void
     */
    public function __construct( FieldRegistry $fieldRegistry, ContentTypeHandler $contentTypeHandler, FieldNameGenerator $nameGenerator )
    {
        $this->fieldRegistry      = $fieldRegistry;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->nameGenerator      = $nameGenerator;
    }

    /**
     * Get field type information
     *
     * @return void
     */
    protected function getFieldTypes()
    {
        if ( $this->fieldTypes !== null )
        {
            return $this->fieldTypes;
        }

        foreach ( $this->contentTypeHandler->loadAllGroups() as $group )
        {
            foreach ( $this->contentTypeHandler->loadContentTypes( $group->id ) as $contentType )
            {
                foreach ( $contentType->fieldDefinitions as $fieldDefinition )
                {
                    $fieldType = $this->fieldRegistry->getType( $fieldDefinition->fieldType );
                    foreach ( $fieldType->getIndexDefinition() as $name => $type )
                    {
                        $this->fieldTypes[$fieldDefinition->identifier][] =
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

