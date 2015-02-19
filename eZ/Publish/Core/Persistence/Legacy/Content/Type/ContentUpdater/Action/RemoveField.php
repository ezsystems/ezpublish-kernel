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
        ContentMapper $contentMapper
    )
    {
        $this->contentGateway = $contentGateway;
        $this->fieldDefinition = $fieldDef;
        $this->storageHandler = $storageHandler;
        $this->contentMapper = $contentMapper;
    }

    /**
     * Applies the action to the given $content
     *
     * @param int $contentId
     */
    public function apply( $contentId )
    {
        $versionNumbers = $this->contentGateway->listVersionNumbers( $contentId );
        $fieldIdSet = array();

        foreach ( $versionNumbers as $versionNo )
        {
            $contentRows = $this->contentGateway->load( $contentId, $versionNo );
            $contentList = $this->contentMapper->extractContentFromRows( $contentRows );
            $content = $contentList[0];
            $versionFieldIdSet = array();

            foreach ( $content->fields as $field )
            {
                if ( $field->fieldDefinitionId == $this->fieldDefinition->id )
                {
                    $fieldIdSet[$field->id] = true;
                    $versionFieldIdSet[$field->id] = true;
                }
            }

            // Delete from external storage with list of IDs per version
            $this->storageHandler->deleteFieldData(
                $this->fieldDefinition->fieldType,
                $content->versionInfo,
                array_keys( $versionFieldIdSet )
            );
        }

        // Delete from internal storage -- field is always deleted from _all_ versions
        foreach ( array_keys( $fieldIdSet ) as $fieldId )
        {
            $this->contentGateway->deleteField( $fieldId );
        }
    }
}
