<?php

/**
 * File containing the ContentTypeGroupList ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRest\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRest\Output\Generator;
use EzSystems\EzPlatformRest\Output\Visitor;

/**
 * ContentTypeGroupList value object visitor.
 */
class ContentTypeGroupList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRest\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRest\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\ContentTypeGroupList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('ContentTypeGroupList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ContentTypeGroupList'));
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader('Accept-Patch', false);

        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadContentTypeGroupList')
        );
        $generator->endAttribute('href');

        $generator->startList('ContentTypeGroup');
        foreach ($data->contentTypeGroups as $contentTypeGroup) {
            $visitor->visitValueObject($contentTypeGroup);
        }
        $generator->endList('ContentTypeGroup');

        $generator->endObjectElement('ContentTypeGroupList');
    }
}
