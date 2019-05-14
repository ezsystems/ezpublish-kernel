<?php

/**
 * File containing the URLAliasList ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRest\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRest\Output\Generator;
use EzSystems\EzPlatformRest\Output\Visitor;

/**
 * URLAliasList value object visitor.
 */
class URLAliasList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRest\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRest\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\URLAliasList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('UrlAliasList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('UrlAliasList'));

        $generator->startAttribute('href', $data->path);
        $generator->endAttribute('href');

        $generator->startList('UrlAlias');
        foreach ($data->urlAliases as $urlAlias) {
            $visitor->visitValueObject($urlAlias);
        }
        $generator->endList('UrlAlias');

        $generator->endObjectElement('UrlAliasList');
    }
}
