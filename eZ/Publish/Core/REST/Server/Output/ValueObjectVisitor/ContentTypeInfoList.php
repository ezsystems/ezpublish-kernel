<?php

/**
 * File containing the ContentTypeInfoList ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRestCommon\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRestCommon\Output\Generator;
use EzSystems\EzPlatformRestCommon\Output\Visitor;
use eZ\Publish\Core\REST\Server\Values;

/**
 * ContentTypeInfoList value object visitor.
 */
class ContentTypeInfoList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRestCommon\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRestCommon\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\ContentTypeInfoList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('ContentTypeInfoList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ContentTypeInfoList'));
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader('Accept-Patch', false);

        $generator->startAttribute('href', $data->path);
        $generator->endAttribute('href');

        $generator->startList('ContentType');
        foreach ($data->contentTypes as $contentType) {
            $visitor->visitValueObject(
                new Values\RestContentType($contentType)
            );
        }
        $generator->endList('ContentType');

        $generator->endObjectElement('ContentTypeInfoList');
    }
}
