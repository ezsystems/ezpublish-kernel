<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;

use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator,
    eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler,
    eZ\Publish\Core\Persistence\Solr\Content\Search\FieldRegistry;

/**
 * Visits the Field criterion
 */
class FieldIn extends CriterionVisitor
{
    /**
     * Field registry
     *
     * @var \eZ\Publish\Core\Persistence\Solr\Content\FieldRegistry
     */
    protected $fieldRegistry;

    /**
     * Content type handler
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

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
     * @return void
     */
    public function __construct( FieldRegistry $fieldRegistry, ContentTypeHandler $contentTypeHandler )
    {
        $this->fieldRegistry      = $fieldRegistry;
        $this->contentTypeHandler = $contentTypeHandler;
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
                    // @TODO: Refactor to field registry, also used in:
                    // Handler.php +290
                    $fieldType = $this->fieldRegistry->getType( $fieldDefinition->fieldType );
                    $prefix    = $contentType->identifier . '/' . $fieldDefinition->identifier . '/';
                    foreach ( $fieldType->getIndexDefinition() as $name => $type )
                    {
                        $this->fieldTypes[$fieldDefinition->identifier][$prefix . $name] = $type;
                    }
                }
            }
        }

        return $this->fieldTypes;
    }

    /**
     * CHeck if visitor is applicable to current criterion
     *
     * @param Criterion $criterion
     * @return bool
     */
    public function canVisit( Criterion $criterion )
    {
        return
            $criterion instanceof Criterion\Field &&
            ( ( $criterion->operator ?: Operator::IN ) === Operator::IN ||
              $criterion->operator === Operator::EQ );
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @param DocumentField $field
     * @return void
     */
    public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null )
    {
        $fieldTypes = $this->getFieldTypes();
        $criterion->value = (array) $criterion->value;

        $queries = array();
        foreach ( $criterion->value as $value )
        {
            foreach ( $fieldTypes[$criterion->target] as $name => $fieldType )
            {
                // @TODO: Fix & extract this (See Gateway\\Native)
                $queries[] = $name . '_s:"' . $value . '"';
            }
        }

        return '(' . implode( ' OR ', $queries ) . ')';
    }
}

