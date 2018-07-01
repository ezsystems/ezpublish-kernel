<?php

/**
 * File containing the ContentTypeCreateStruct visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * ContentTypeCreateStruct value object visitor.
 */
class ContentTypeCreateStruct extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.

     *
*@param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct $contentCreateStruct
     */
    public function visit(Visitor $visitor, Generator $generator, $contentCreateStruct)
    {
        $generator->startObjectElement('ContentTypeCreate');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ContentTypeCreate'));

        $generator->startValueElement('identifier', $contentCreateStruct->identifier);
        $generator->endValueElement('identifier');

        $generator->startValueElement('remoteId', $contentCreateStruct->remoteId);
        $generator->endValueElement('remoteId');

        $generator->startValueElement('urlAliasSchema', $contentCreateStruct->urlAliasSchema);
        $generator->endValueElement('urlAliasSchema');

        $generator->startValueElement('nameSchema', $contentCreateStruct->nameSchema);
        $generator->endValueElement('nameSchema');

        $generator->startValueElement('isContainer', ( $contentCreateStruct->isContainer ? 'true' : 'false'));
        $generator->endValueElement('isContainer');

        $generator->startValueElement('mainLanguageCode', $contentCreateStruct->mainLanguageCode);
        $generator->endValueElement('mainLanguageCode');

        $generator->startValueElement('defaultAlwaysAvailable', ( $contentCreateStruct->defaultAlwaysAvailable ? 'true' : 'false'));
        $generator->endValueElement('defaultAlwaysAvailable');

        $generator->startValueElement('defaultSortField', $this->serializeSortField($contentCreateStruct->defaultSortField));
        $generator->endValueElement('defaultSortField');

        $generator->startValueElement('defaultSortOrder', $this->serializeSortOrder($contentCreateStruct->defaultSortOrder));
        $generator->endValueElement('defaultSortOrder');

        if (!empty( $contentCreateStruct->names)) {
            $generator->startHashElement('names');
            $generator->startList('value');
            foreach ($contentCreateStruct->names as $languageCode => $name) {
                $generator->startValueElement('value', $name, array('languageCode' => $languageCode));
                $generator->endValueElement('value');
            }
            $generator->endList('value');
            $generator->endHashElement('names');
        }

        if (!empty( $contentCreateStruct->descriptions)) {
            $generator->startHashElement('descriptions');
            $generator->startList('value');
            foreach ($contentCreateStruct->descriptions as $languageCode => $description) {
                $generator->startValueElement('value', $description, array('languageCode' => $languageCode));
                $generator->endValueElement('value');
            }
            $generator->endList('value');
            $generator->endHashElement('descriptions');
        }

        if ($contentCreateStruct->creationDate !== null) {
            $generator->startValueElement('modificationDate', $contentCreateStruct->creationDate->format('c'));
            $generator->endValueElement('modificationDate');
        }

        if ($contentCreateStruct->creatorId !== null) {
            $generator->startObjectElement('User');
            $generator->startAttribute('href', $contentCreateStruct->creatorId);
            $generator->endAttribute('href');
            $generator->endObjectElement('User');
        }

        if (!empty( $contentCreateStruct->fieldDefinitions)) {
            $generator->startHashElement('FieldDefinitions');
            $generator->startList('FieldDefinition');
            foreach ($contentCreateStruct->fieldDefinitions as $fieldDefinitionCreateStruct) {
                $visitor->visitValueObject($fieldDefinitionCreateStruct);
            }
            $generator->endList('FieldDefinition');
            $generator->endHashElement('FieldDefinitions');
        }

        $generator->endObjectElement('ContentTypeCreate');
    }
}
