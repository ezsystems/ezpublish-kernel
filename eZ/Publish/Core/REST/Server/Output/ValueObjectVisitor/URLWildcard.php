<?php

/**
 * File containing the URLWildcard ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\API\Repository\Values\Content\URLWildcard as URLWildcardValue;

/**
 * URLWildcard value object visitor.
 */
class URLWildcard extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\API\Repository\Values\Content\URLWildcard $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $visitor->setHeader('Content-Type', $generator->getMediaType('UrlWildcard'));
        $generator->startObjectElement('UrlWildcard');
        $this->visitURLWildcardAttributes($visitor, $generator, $data);
        $generator->endObjectElement('UrlWildcard');
    }

    protected function visitURLWildcardAttributes(Visitor $visitor, Generator $generator, URLWildcardValue $data)
    {
        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadURLWildcard', ['urlWildcardId' => $data->id])
        );
        $generator->endAttribute('href');

        $generator->startAttribute('id', $data->id);
        $generator->endAttribute('id');

        $generator->startValueElement('sourceUrl', $data->sourceUrl);
        $generator->endValueElement('sourceUrl');

        $generator->startValueElement('destinationUrl', $data->destinationUrl);
        $generator->endValueElement('destinationUrl');

        $generator->startValueElement(
            'forward',
            $this->serializeBool($generator, $data->forward)
        );
        $generator->endValueElement('forward');
    }
}
