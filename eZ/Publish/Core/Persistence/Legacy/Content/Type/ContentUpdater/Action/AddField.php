<?php
/**
 * File containing the content updater add field action class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\ContentUpdater\Action,
    eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue,
    eZ\Publish\Core\Persistence\Legacy\Content\Gateway,
    eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;

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
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter
     */
    protected $fieldValueConverter;

    /**
     * Creates a new action
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway $contentGateway
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter $converter
     */
    public function __construct(
        Gateway $contentGateway,
        FieldDefinition $fieldDef,
        Converter $converter )
    {
        $this->contentGateway = $contentGateway;
        $this->fieldDefinition = $fieldDef;
        $this->fieldValueConverter = $converter;
    }

    /**
     * Applies the action to the given $content
     *
     * @param Content $content
     * @return void
     * @todo Handle external field data
     */
    public function apply( Content $content )
    {
        $field = $this->createField( $content );

        $storageValue = new StorageFieldValue();
        $this->fieldValueConverter->toStorageValue(
            $field->value,
            $storageValue
        );

        $field->id = $this->contentGateway->insertNewField(
            $content,
            $field,
            $storageValue
        );

        $content->fields[] = $field;
    }

    /**
     *
     *
     * @param Content $content
     * @return void
     * @todo Handle ->languageCode
     */
    protected function createField( Content $content )
    {
        $field = new Content\Field();
        $field->fieldDefinitionId = $this->fieldDefinition->id;
        $field->type = $this->fieldDefinition->fieldType;
        $field->value = clone $this->fieldDefinition->defaultValue;
        $field->versionNo = $content->versionInfo->versionNo;
        //$field->languageCode = $content->initialLanguageId;
        return $field;
    }
}
