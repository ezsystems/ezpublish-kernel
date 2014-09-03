<?php
/**
 * File containing the content updater remove field action class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action;

use eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway as ContentGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Mapper as ContentMapper;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;

/**
 * Action to remove a field from content objects
 */
class RemoveField extends Action
{
    /**
     * Field definition of the field to remove
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    protected $fieldDefinition;

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
     * Creates a new action
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway $contentGateway
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler $storageHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Mapper $contentMapper
     */
    public function __construct(
        ContentGateway $contentGateway,
        FieldDefinition $fieldDef,
        StorageHandler $storageHandler,
        ContentMapper $contentMapper )
    {
        $this->contentGateway = $contentGateway;
        $this->fieldDefinition = $fieldDef;
        $this->storageHandler = $storageHandler;
        $this->contentMapper = $contentMapper;
    }

    /**
     * Applies the action to the given $content
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ContentInfo $contentInfo
     *
     * @return void
     */
    public function apply( ContentInfo $contentInfo )
    {
        $fieldIdsToRemoveMap = array();

        $contentRows = $this->contentGateway->load( $contentInfo->id, $contentInfo->currentVersionNo );
        $contentList = $this->contentMapper->extractContentFromRows( $contentRows );
        $content = $contentList[0];

        foreach ( $content->fields as $field )
        {
            if ( $field->fieldDefinitionId == $this->fieldDefinition->id )
            {
                $this->contentGateway->deleteField( $field->id );
                $fieldIdsToRemoveMap[$field->type][] = $field->id;
            }
        }

        foreach ( $fieldIdsToRemoveMap as $fieldType => $ids )
        {
            $this->storageHandler->deleteFieldData( $fieldType, $content->versionInfo, $ids );
        }
    }
}
