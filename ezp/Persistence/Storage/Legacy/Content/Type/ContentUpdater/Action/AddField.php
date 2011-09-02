<?php
/**
 * File containing the content updater add field action class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater\Action;
use ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater\Action,
    ezp\Persistence\Content,
    ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter,
    ezp\Persistence\Storage\Legacy\Content\Gateway,
    ezp\Persistence\Content\Type\FieldDefinition;

/**
 * Action to add a field to content objects
 */
class AddField extends Action
{
    /**
     * Field definition of the field to add
     *
     * @var mixed
     */
    protected $fieldDefinition;

    /**
     * Field value converter
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter
     */
    protected $fieldValueConverter;

    /**
     * Creates a new action
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Gateway $contentGateway
     * @param \ezp\Persistence\Content\Type\FieldDefinition
     */
    public function __construct(
        Gateway $contentGateway,
        FieldDefinition $fieldDef,
        Converter $converter )
    {
        $this->contentGateway      = $contentGateway;
        $this->fieldDefinition     = $fieldDef;
        $this->fieldValueConverter = $converter;
    }

    /**
     * Applies the action to the given $content
     *
     * @param Content $content
     * @return void
     */
    public function apply( Content $content )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }
}
