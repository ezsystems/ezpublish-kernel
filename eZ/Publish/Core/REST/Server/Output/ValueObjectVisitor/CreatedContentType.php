<?php

/**
 * File containing the CreatedContentType ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRest\Output\Generator;
use EzSystems\EzPlatformRest\Output\Visitor;

/**
 * CreatedContentType value object visitor.
 *
 * @todo coverage add test
 */
class CreatedContentType extends RestContentType
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRest\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRest\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\CreatedContentType $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $restContentType = $data->contentType;

        parent::visit($visitor, $generator, $restContentType);
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ezpublish_rest_loadContentType' . $this->getUrlTypeSuffix($restContentType->contentType->status),
                array(
                    'contentTypeId' => $restContentType->contentType->id,
                )
            )
        );
        $visitor->setStatus(201);
    }
}
