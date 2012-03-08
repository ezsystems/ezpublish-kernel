<?php
/**
 * File containing the content updater remove field action class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action,
    eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\Core\Persistence\Legacy\Content\Gateway as ContentGateway,
    eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;

/**
 * Action to remove a field from content objects
 */
class RemoveField extends Action
{
    /**
     * Field definition of the field to add
     *
     * @var mixed
     */
    protected $fieldDefinition;

    /**
     * Creates a new action
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway $contentGateway
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    public function __construct(
        ContentGateway $contentGateway,
        FieldDefinition $fieldDef )
    {
        $this->contentGateway = $contentGateway;
        $this->fieldDefinition = $fieldDef;
    }
    /**
     * Applies the action to the given $content
     *
     * @param Content $content
     * @return void
     * @todo Handle external field data.
     */
    public function apply( Content $content )
    {
        foreach ( $content->fields as $field )
        {
            if ( $field->fieldDefinitionId == $this->fieldDefinition->id )
            {
                $this->contentGateway->deleteField(
                    $field->id, $field->versionNo
                );
            }
        }
    }
}
