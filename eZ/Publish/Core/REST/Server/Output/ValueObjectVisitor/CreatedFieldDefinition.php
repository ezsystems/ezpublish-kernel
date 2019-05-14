<?php

/**
 * File containing the CreatedFieldDefinition ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRest\Output\Generator;
use EzSystems\EzPlatformRest\Output\Visitor;

/**
 * CreatedFieldDefinition value object visitor.
 *
 * @todo coverage add test
 */
class CreatedFieldDefinition extends RestFieldDefinition
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRest\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRest\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\CreatedFieldDefinition $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $restFieldDefinition = $data->fieldDefinition;

        parent::visit($visitor, $generator, $restFieldDefinition);

        $draftUriPart = $this->getUrlTypeSuffix($restFieldDefinition->contentType->status);
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                "ezpublish_rest_loadContentType{$draftUriPart}FieldDefinition",
                array(
                    'contentTypeId' => $restFieldDefinition->contentType->id,
                    'fieldDefinitionId' => $restFieldDefinition->fieldDefinition->id,
                )
            )
        );
        $visitor->setStatus(201);
    }
}
