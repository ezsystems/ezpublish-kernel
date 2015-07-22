<?php

/**
 * File containing the URLWildcardList ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * URLWildcardList value object visitor.
 */
class URLWildcardList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
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
