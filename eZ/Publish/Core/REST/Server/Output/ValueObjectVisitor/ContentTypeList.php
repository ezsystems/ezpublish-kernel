<?php

/**
 * File containing the ContentTypeList ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRest\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRest\Output\Generator;
use EzSystems\EzPlatformRest\Output\Visitor;
use eZ\Publish\Core\REST\Server\Values;

/**
 * ContentTypeList value object visitor.
 */
class ContentTypeList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRest\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRest\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\ContentTypeList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('ContentTypeList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ContentTypeList'));
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader('Accept-Patch', false);

        $generator->startAttribute('href', $data->path);
        $generator->endAttribute('href');

        $generator->startList('ContentType');
        foreach ($data->contentTypes as $contentType) {
            $visitor->visitValueObject(
                new Values\RestContentType(
                    $contentType,
                    $contentType->getFieldDefinitions()
                )
            );
        }
        $generator->endList('ContentType');

        $generator->endObjectElement('ContentTypeList');
    }
}
