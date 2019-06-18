<?php

/**
 * File containing the FieldDefinitionList ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Server\Values\RestFieldDefinition as ValuesRestFieldDefinition;

/**
 * FieldDefinitionList value object visitor.
 */
class FieldDefinitionList extends RestContentTypeBase
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\FieldDefinitionList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $fieldDefinitionList = $data;
        $contentType = $fieldDefinitionList->contentType;

        $urlTypeSuffix = $this->getUrlTypeSuffix($contentType->status);

        $generator->startObjectElement('FieldDefinitions', 'FieldDefinitionList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('FieldDefinitionList'));
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader('Accept-Patch', false);

        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_loadContentType' . $urlTypeSuffix . 'FieldDefinitionList',
                [
                    'contentTypeId' => $contentType->id,
                ]
            )
        );
        $generator->endAttribute('href');

        $generator->startList('FieldDefinition');
        foreach ($fieldDefinitionList->fieldDefinitions as $fieldDefinition) {
            $visitor->visitValueObject(
                new ValuesRestFieldDefinition($contentType, $fieldDefinition)
            );
        }
        $generator->endList('FieldDefinition');

        $generator->endObjectElement('FieldDefinitions');
    }
}
