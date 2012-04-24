<?php
/**
 * File containing the content updater class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Type;
use eZ\Publish\Core\Persistence\Legacy\Content,
    eZ\Publish\Core\Persistence\Legacy\Content\Search\Handler as SearchHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Gateway as ContentGateway,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Registry,
    eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater,
    eZ\Publish\SPI\Persistence\Content\Type,
    eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Class to update content objects to a new type version
 */
class ContentUpdater
{
    /**
     * Content gateway
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected $contentGateway;

    /**
     * FieldValue converter registry
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Registry
     */
    protected $converterRegistry;

    /**
     * Search handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\Handler
     */
    protected $searchHandler;

    /**
     * Creates a new content updater
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway $contentTypeGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway $contentGateway
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
        foreach ( $this->loadContentObjects( $contentTypeId ) as $content )
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
        return $this->searchHandler->find( new Criterion\ContentTypeId( $contentTypeId ) );
    }
}
