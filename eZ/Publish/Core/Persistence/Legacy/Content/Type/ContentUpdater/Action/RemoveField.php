<?php
/**
 * File containing the content updater remove field action class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
     * @var mixed
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
     * @param Content $content
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
                $this->contentGateway->deleteField(
                    $field->id, $field->versionNo
                );
                $fieldIdsToRemoveMap[$field->type][] = $field->id;
            }
        }

        foreach ( $fieldIdsToRemoveMap as $fieldType => $ids )
        {
            $this->storageHandler->deleteFieldData( $fieldType, $content->versionInfo, $ids );
        }
    }
}
