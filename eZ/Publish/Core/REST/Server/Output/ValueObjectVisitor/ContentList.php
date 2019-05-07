<?php

/**
 * File containing the ContentList ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRestCommon\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRestCommon\Output\Generator;
use EzSystems\EzPlatformRestCommon\Output\Visitor;

/**
 * ContentList value object visitor.
 */
class ContentList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRestCommon\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRestCommon\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\ContentList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('ContentList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ContentList'));
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader('Accept-Patch', false);

        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_redirectContent')
        );
        $generator->endAttribute('href');

        $generator->startList('ContentInfo');
        foreach ($data->contents as $content) {
            $visitor->visitValueObject($content);
        }
        $generator->endList('ContentInfo');

        $generator->endObjectElement('ContentList');
    }
}
