<?php

/**
 * File containing the ContentCreateStruct visitor class.
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
 * ContentCreateStruct value object visitor.
 */
class RestContentCreateStruct extends ValueObjectVisitor
{
    public function __construct(FieldTypeSerializer $fieldTypeSerializer)
    {
        $this->fieldTypeSerializer = $fieldTypeSerializer;
    }

    /**
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\RestContentCreateStruct $restContentCreateStruct
     */
    public function visit(Visitor $visitor, Generator $generator, $restContentCreateStruct)
    {
        $contentCreateStruct = $restContentCreateStruct->contentCreateStruct;

        $generator->startObjectElement('ContentCreate');
        $visitor->setHeader('Content-Type', $generator->getMediaType('TypeCreate'));

        $generator->startValueElement('mainLanguageCode', $contentCreateStruct->mainLanguageCode);
        $generator->endValueElement('mainLanguageCode');

        $generator->startValueElement('alwaysAvailable', $contentCreateStruct->alwaysAvailable ? 'true' : 'false');
        $generator->endValueElement('alwaysAvailable');

        $generator->startValueElement('remoteId', (int)$contentCreateStruct->remoteId);
        $generator->endValueElement('remoteId');

        if ($contentCreateStruct->modificationDate !== null) {
            $generator->startValueElement('modificationDate', $contentCreateStruct->modificationDate->format('c'));
            $generator->endValueElement('modificationDate');
        }

        if ($contentCreateStruct->ownerId !== null) {
            $generator->startObjectElement('User');
            $generator->startAttribute('href', $contentCreateStruct->ownerId);
            $generator->endAttribute('href');
            $generator->endObjectElement('User');
        }

        $generator->startObjectElement('ContentType');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_loadContentType',
                array('contentTypeId' => $contentCreateStruct->contentType->id)
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('ContentType');

        if (!empty($contentCreateStruct->fields)) {
            $generator->startHashElement('fields');
            $generator->startList('field');

            foreach ($contentCreateStruct->fields as $field) {
                $this->visitField($generator, $contentCreateStruct->contentType, $field);
            }
            $generator->endList('field');
            $generator->endHashElement('fields');
        }

        $visitor->visitValueObject($restContentCreateStruct->locationCreateStruct);

        $generator->endObjectElement('ContentCreate');
    }

    /**
     * Visits a single content field and generates its content.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     */
    public function visitField(Generator $generator, ContentType $contentType, Field $field)
    {
        $generator->startHashElement('field');

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
