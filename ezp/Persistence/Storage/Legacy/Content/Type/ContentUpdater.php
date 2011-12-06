<?php
/**
 * File containing the content updater class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Type;
use ezp\Persistence\Storage\Legacy\Content,
    ezp\Persistence\Storage\Legacy\Content\Search\Handler as SearchHandler,
    ezp\Persistence\Storage\Legacy\Content\Gateway as ContentGateway,
    ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry,
    ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater,
    ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Content\Query\Criterion;

/**
 * Class to update content objects to a new type version
 */
class ContentUpdater
{
    /**
     * Content gateway
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Gateway
     */
    protected $contentGateway;

    /**
     * FieldValue converter registry
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry
     */
    protected $converterRegistry;

    /**
     * Search handler
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Search\Handler
     */
    protected $searchHandler;

    /**
     * Creates a new content updater
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Type\Gateway $contentTypeGateway
     * @param \ezp\Persistence\Storage\Legacy\Content\Gateway $contentGateway
     */
    public function __construct(
        SearchHandler $searchHandler,
        ContentGateway $contentGateway,
        Registry $converterRegistry )
    {
        $this->searchHandler = $searchHandler;
        $this->contentGateway = $contentGateway;
        $this->converterRegistry = $converterRegistry;
    }

    /**
     * Determines the neccessary update actions
     *
     * @param mixed $contentTypeId
     * @return ContentUpdater\Action[]
     */
    public function determineActions( Type $fromType, Type $toType )
    {
        $actions = array();
        foreach ( $fromType->fieldDefinitions as $fieldDef )
        {
            if ( !$this->hasFieldDefinition( $toType, $fieldDef ) )
            {
                $actions[] = new ContentUpdater\Action\RemoveField(
                    $this->contentGateway,
                    $fieldDef
                );
            }
        }
        foreach ( $toType->fieldDefinitions as $fieldDef )
        {
            if ( !$this->hasFieldDefinition( $fromType, $fieldDef ) )
            {
                $actions[] = new ContentUpdater\Action\AddField(
                    $this->contentGateway,
                    $fieldDef,
                    $this->converterRegistry->getConverter(
                        $fieldDef->fieldType
                    )
                );
            }
        }
        return $actions;
    }

    /**
     * hasFieldDefinition
     *
     * @param Content\Type $type
     * @param Content\Type\FieldDefinition $fieldDef
     * @return void
     */
    protected function hasFieldDefinition( Type $type, FieldDefinition $fieldDef )
    {
        foreach ( $type->fieldDefinitions as $existFieldDef )
        {
            if ( $existFieldDef->id == $fieldDef->id )
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Applies all given updates
     *
     * @param mixed $contentTypeId
     * @param ContentUpdater\Action[] $actions
     * @return void
     */
    public function applyUpdates( $contentTypeId, array $actions )
    {
        $contentObjs = $this->loadContentObjects( $contentTypeId );
        foreach ( $contentObjs as $content )
        {
            foreach ( $actions as $action )
            {
                $action->apply( $content );
            }
        }
    }

    /**
     * Returns all content objects of $contentTypeId
     *
     * @param mixed $contentTypeId
     * @return Content[]
     */
    protected function loadContentObjects( $contentTypeId )
    {
        $criterion = new Criterion\ContentTypeId( $contentTypeId );
        return $this->searchHandler->find( $criterion );
    }
}
