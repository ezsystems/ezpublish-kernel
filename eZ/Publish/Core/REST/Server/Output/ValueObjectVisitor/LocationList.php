<?php

/**
 * File containing the LocationList ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * LocationList value object visitor.
 */
class LocationList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\LocationList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('LocationList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('LocationList'));

        $generator->startAttribute('href', $data->path);
        $generator->endAttribute('href');

        $generator->startList('Location');

        foreach ($data->locations as $restLocation) {
            $generator->startObjectElement('Location');
            $generator->startAttribute(
                'href',
                $this->router->generate(
                    'ezpublish_rest_loadLocation',
                    ['locationPath' => trim($restLocation->location->pathString, '/')]
                )
            );
            $generator->endAttribute('href');
            $generator->endObjectElement('Location');
        }

        $generator->endList('Location');

        $generator->endObjectElement('LocationList');
    }
}
