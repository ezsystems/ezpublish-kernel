<?php

/**
 * File containing the RestFieldDefinition ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Common\Output\FieldTypeSerializer;
use eZ\Publish\API\Repository\Values\ContentType\ContentType as APIContentType;

/**
 * RestFieldDefinition value object visitor.
 *
 * @todo $fieldSettings & $validatorConfiguration (missing from spec)
 */
class RestFieldDefinition extends RestContentTypeBase
{
    /**
     * @var \eZ\Publish\Core\REST\Common\Output\FieldTypeSerializer
     */
    protected $fieldTypeSerializer;

    /**
     * @param \eZ\Publish\Core\REST\Common\Output\FieldTypeSerializer $fieldTypeSerializer
     */
    public function __construct(FieldTypeSerializer $fieldTypeSerializer)
    {
        $this->fieldTypeSerializer = $fieldTypeSerializer;
    }

    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\RestFieldDefinition $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $restFieldDefinition = $data;
        $fieldDefinition = $restFieldDefinition->fieldDefinition;
        $contentType = $restFieldDefinition->contentType;

        $urlTypeSuffix = $this->getUrlTypeSuffix($contentType->status);

        $generator->startObjectElement('FieldDefinition');
        $visitor->setHeader('Content-Type', $generator->getMediaType('FieldDefinition'));

        if ($contentType->status === APIContentType::STATUS_DRAFT) {
            $visitor->setHeader('Accept-Patch', $generator->getMediaType('FieldDefinitionUpdate'));
        }

        $generator->startAttribute(
            'href',
            $this->router->generate(
                "ezpublish_rest_loadContentType{$urlTypeSuffix}FieldDefinition",
                array(
                    'contentTypeId' => $contentType->id,
                    'fieldDefinitionId' => $fieldDefinition->id,
                )
            )
        );
        $generator->endAttribute('href');

        $generator->startValueElement('id', $fieldDefinition->id);
        $generator->endValueElement('id');

        $generator->startValueElement('identifier', $fieldDefinition->identifier);
        $generator->endValueElement('identifier');

        $generator->startValueElement('fieldType', $fieldDefinition->typeIdentifier);
        $generator->endValueElement('fieldType');

        $generator->startValueElement('fieldGroup', $fieldDefinition->fieldGroup);
        $generator->endValueElement('fieldGroup');

        $generator->startValueElement('position', $fieldDefinition->position);
        $generator->endValueElement('position');

        $generator->startValueElement(
            'isTranslatable',
            $this->serializeBool($generator, $fieldDefinition->isTranslatable)
        );
        $generator->endValueElement('isTranslatable');

        $generator->startValueElement(
            'isRequired',
            $this->serializeBool($generator, $fieldDefinition->isRequired)
        );
        $generator->endValueElement('isRequired');

        $generator->startValueElement(
            'isInfoCollector',
            $this->serializeBool($generator, $fieldDefinition->isInfoCollector)
        );
        $generator->endValueElement('isInfoCollector');

        $this->fieldTypeSerializer->serializeFieldDefaultValue(
            $generator,
            $fieldDefinition->typeIdentifier,
            $fieldDefinition->defaultValue
        );

        $generator->startValueElement(
            'isSearchable',
            $this->serializeBool($generator, $fieldDefinition->isSearchable)
        );
        $generator->endValueElement('isSearchable');

        $this->visitNamesList($generator, $fieldDefinition->getNames());

        $descriptions = $fieldDefinition->getDescriptions();
        if (is_array($descriptions)) {
            $this->visitDescriptionsList($generator, $descriptions);
        }

        $this->fieldTypeSerializer->serializeFieldSettings(
            $generator,
            $fieldDefinition->typeIdentifier,
            $fieldDefinition->getFieldSettings()
        );

        $this->fieldTypeSerializer->serializeValidatorConfiguration(
            $generator,
            $fieldDefinition->typeIdentifier,
            $fieldDefinition->getValidatorConfiguration()
        );

        $generator->endObjectElement('FieldDefinition');
    }
}
