<?php

/**
 * File containing the URLAliasRefList ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRest\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRest\Output\Generator;
use EzSystems\EzPlatformRest\Output\Visitor;

/**
 * URLAliasRefList value object visitor.
 */
class URLAliasRefList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRest\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRest\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\URLAliasRefList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('UrlAliasRefList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('UrlAliasRefList'));

        $generator->startAttribute('href', $data->path);
        $generator->endAttribute('href');

        $generator->startList('UrlAlias');
        foreach ($data->urlAliases as $urlAlias) {
            $generator->startObjectElement('UrlAlias');

            $generator->startAttribute(
                'href',
                $this->router->generate('ezpublish_rest_loadURLAlias', array('urlAliasId' => $urlAlias->id))
            );
            $generator->endAttribute('href');

            $generator->endObjectElement('UrlAlias');
        }
        $generator->endList('UrlAlias');

        $generator->endObjectElement('UrlAliasRefList');
    }
}
