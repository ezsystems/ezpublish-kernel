<?php

/**
 * File containing the URLWildcardList ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRestCommon\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRestCommon\Output\Generator;
use EzSystems\EzPlatformRestCommon\Output\Visitor;

/**
 * URLWildcardList value object visitor.
 */
class URLWildcardList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRestCommon\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRestCommon\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\URLWildcardList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('UrlWildcardList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('UrlWildcardList'));

        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_listURLWildcards')
        );
        $generator->endAttribute('href');

        $generator->startList('UrlWildcard');
        foreach ($data->urlWildcards as $urlWildcard) {
            $visitor->visitValueObject($urlWildcard);
        }
        $generator->endList('UrlWildcard');

        $generator->endObjectElement('UrlWildcardList');
    }
}
