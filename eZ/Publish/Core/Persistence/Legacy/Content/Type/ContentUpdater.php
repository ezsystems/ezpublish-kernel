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
    eZ\Publish\SPI\Persistence\Content\Search\Handler as SearchHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Gateway as ContentGateway,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry as Registry,
    eZ\Publish\SPI\Persistence\Content\Type,
    eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition,
    eZ\Publish\API\Repository\Values\Content\Query,
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
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    protected $converterRegistry;

    /**
     * Search handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\Handler
     */
    protected $searchHandler;

    /**
     * Storage handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
     */
    protected $storageHandler;

    /**
     * Creates a new content updater
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway $contentGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry $converterRegistry
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler $storageHandler
     */
    public function __construct(
        SearchHandler $searchHandler,
        ContentGateway $contentGateway,
        Registry $converterRegistry,
        StorageHandler $storageHandler )
    {
        $this->searchHandler = $searchHandler;
        $this->contentGateway = $contentGateway;
        $this->converterRegistry = $converterRegistry;
        $this->storageHandler = $storageHandler;
    }

    /**
     * Determines the neccessary update actions
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $fromType
     * @param \eZ\Publish\SPI\Persistence\Content\Type $toType
     *
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
                    $fieldDef,
                    $this->storageHandler
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
                    ),
                    $this->storageHandler
                );
            }
        }
        return $actions;
    }

    /**
     * hasFieldDefinition
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $type
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     *
     * @return boolean
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
        $result = $this->searchHandler->findContent( new Query( array(
            'criterion' => new Criterion\ContentTypeId( $contentTypeId )
        ) ) );

        $content = array();
        foreach ( $result->searchHits as $hit )
        {
            $content[] = $hit->valueObject;
        }

        return $content;
    }
}
