<?php
/**
 * File containing the content updater class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Type;

use eZ\Publish\SPI\Search\Content\Handler as SearchHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway as ContentGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry as Registry;
use eZ\Publish\Core\Persistence\Legacy\Content\Mapper as ContentMapper;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

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
     * Storage handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
     */
    protected $storageHandler;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected $contentMapper;

    /**
     * Creates a new content updater
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway $contentGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry $converterRegistry
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler $storageHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Mapper $contentMapper
     */
    public function __construct(
        ContentGateway $contentGateway,
        Registry $converterRegistry,
        StorageHandler $storageHandler,
        ContentMapper $contentMapper )
    {
        $this->contentGateway = $contentGateway;
        $this->converterRegistry = $converterRegistry;
        $this->storageHandler = $storageHandler;
        $this->storageHandler = $storageHandler;
        $this->contentMapper = $contentMapper;
    }

    /**
     * Determines the necessary update actions
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $fromType
     * @param \eZ\Publish\SPI\Persistence\Content\Type $toType
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action[]
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
                    $this->storageHandler,
                    $this->contentMapper
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
                    $this->storageHandler,
                    $this->contentMapper
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
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action[] $actions
     *
     * @return void
     */
    public function applyUpdates( $contentTypeId, array $actions )
    {
        if ( empty( $actions ) )
        {
            return;
        }

        foreach ( $this->getContentIdsByContentTypeId( $contentTypeId ) as $contentId )
        {
            foreach ( $actions as $action )
            {
                $action->apply( $contentId );
            }
        }
    }

    /**
     * Returns all content objects of $contentTypeId
     *
     * @param mixed $contentTypeId
     *
     * @return int[]
     */
    protected function getContentIdsByContentTypeId( $contentTypeId )
    {
        return $this->contentGateway->getContentIdsByContentTypeId( $contentTypeId );
    }
}
