<?php

/**
 * File containing the FieldDefinitionCreateStruct visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\FieldTypeSerializer;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * FieldDefinitionCreateStruct value object visitor.
 */
class FieldDefinitionCreateStruct extends ValueObjectVisitor
{
    /** @var \eZ\Publish\Core\REST\Common\Output\FieldTypeSerializer */
    protected $fieldTypeSerializer;

    /**
     * @param \eZ\Publish\Core\REST\Common\RequestParser $requestParser
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
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct $fieldDefinitionCreateStruct
     */
    public function visit(Visitor $visitor, Generator $generator, $fieldDefinitionCreateStruct)
    {
        $generator->startObjectElement('FieldDefinition');
        $visitor->setHeader('Content-Type', $generator->getMediaType('FieldDefinitionCreateStruct'));

        $generator->startValueElement('identifier', $fieldDefinitionCreateStruct->identifier);
        $generator->endValueElement('identifier');

        $generator->startValueElement('fieldType', $fieldDefinitionCreateStruct->fieldTypeIdentifier);
        $generator->endValueElement('fieldType');

        $generator->startValueElement('fieldGroup', $fieldDefinitionCreateStruct->fieldGroup);
        $generator->endValueElement('fieldGroup');

        $generator->startValueElement('position', $fieldDefinitionCreateStruct->position);
        $generator->endValueElement('position');

        $generator->startValueElement('isTranslatable', $fieldDefinitionCreateStruct->isTranslatable ? 'true' : 'false');
        $generator->endValueElement('isTranslatable');

        $generator->startValueElement('isRequired', $fieldDefinitionCreateStruct->isRequired ? 'true' : 'false');
        $generator->endValueElement('isRequired');

        $generator->startValueElement('isInfoCollector', $fieldDefinitionCreateStruct->isInfoCollector ? 'true' : 'false');
        $generator->endValueElement('isInfoCollector');

        $generator->startValueElement('isSearchable', $fieldDefinitionCreateStruct->isSearchable ? 'true' : 'false');
        $generator->endValueElement('isSearchable');

        $this->fieldTypeSerializer->serializeFieldDefaultValue(
            $generator,
            $fieldDefinitionCreateStruct->fieldTypeIdentifier,
            $fieldDefinitionCreateStruct->defaultValue
        );

        $this->fieldTypeSerializer->serializeFieldSettings(
            $generator,
            $fieldDefinitionCreateStruct->fieldTypeIdentifier,
            $fieldDefinitionCreateStruct->fieldSettings
        );

        $this->fieldTypeSerializer->serializeValidatorConfiguration(
            $generator,
            $fieldDefinitionCreateStruct->fieldTypeIdentifier,
            $fieldDefinitionCreateStruct->validatorConfiguration
        );

        if (!empty($fieldDefinitionCreateStruct->names)) {
            $generator->startHashElement('names');
            $generator->startList('value');
            foreach ($fieldDefinitionCreateStruct->names as $languageCode => $name) {
                $generator->startValueElement('value', $name, array('languageCode' => $languageCode));
                $generator->endValueElement('value');
            }
            $generator->endList('value');
            $generator->endHashElement('names');
        }

        if (!empty($fieldDefinitionCreateStruct->descriptions)) {
            $generator->startHashElement('descriptions');
            $generator->startList('value');
            foreach ($fieldDefinitionCreateStruct->descriptions as $languageCode => $description) {
                $generator->startValueElement('value', $description, array('languageCode' => $languageCode));
                $generator->endValueElement('value');
            }
            $generator->endList('value');
            $generator->endHashElement('descriptions');
        }

        $generator->endObjectElement('FieldDefinition');
    }
}
