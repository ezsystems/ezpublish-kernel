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
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway as ContentGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler;
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
     * Creates a new action
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway $contentGateway
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler $storageHandler
     */
    public function __construct(
        ContentGateway $contentGateway,
        FieldDefinition $fieldDef,
        StorageHandler $storageHandler )
    {
        $this->contentGateway = $contentGateway;
        $this->fieldDefinition = $fieldDef;
        $this->storageHandler = $storageHandler;
    }

    /**
     * Applies the action to the given $content
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return void
     */
    public function apply( Content $content )
    {
        $fieldIdsToRemoveMap = array();

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
