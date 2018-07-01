<?php

/**
 * This file is part of the ezpublish-kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\REST\Common\Output\FieldTypeSerializer;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * Visits a ContentUpdateStruct into a VersionUpdate request.
 */
class VersionUpdate extends ValueObjectVisitor
{
    /**
     * @param \eZ\Publish\Core\REST\Common\Output\FieldTypeSerializer $fieldTypeSerializer
     */
    public function __construct(FieldTypeSerializer $fieldTypeSerializer)
    {
        $this->fieldTypeSerializer = $fieldTypeSerializer;
    }

    /**
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Client\Values\Content\VersionUpdate $versionUpdate
     */
    public function visit(Visitor $visitor, Generator $generator, $versionUpdate)
    {
        $updateStruct = $versionUpdate->contentUpdateStruct;

        $generator->startObjectElement('VersionUpdate');
        $visitor->setHeader('Content-Type', $generator->getMediaType('VersionUpdate'));
        $generator->startValueElement('initialLanguageCode', $updateStruct->initialLanguageCode);
        $generator->endValueElement('initialLanguageCode');

        if (is_array($updateStruct->fields) && count($updateStruct->fields) > 0) {
            $generator->startHashElement('fields');
            $generator->startList('field');
            foreach ($updateStruct->fields as $field) {
                $this->visitField($generator, $versionUpdate->contentType, $field);
            }
            $generator->endList('field');
            $generator->endHashElement('fields');
        }

        $generator->endObjectElement('VersionUpdate');
    }

    private function visitField(Generator $generator, ContentType $contentType, Field $field)
    {
        $generator->startHashElement('field');

        $generator->startValueElement('id', $field->id);
        $generator->endValueElement('id');

        $generator->startValueElement('fieldDefinitionIdentifier', $field->fieldDefIdentifier);
        $generator->endValueElement('fieldDefinitionIdentifier');

        $generator->startValueElement('languageCode', $field->languageCode);
        $generator->endValueElement('languageCode');

        $this->fieldTypeSerializer->serializeFieldValue(
            $generator,
            $contentType,
            $field
        );

        $generator->endHashElement('field');
    }
}
