<?php

/**
 * File containing the Root ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * Root value object visitor.
 */
class Root extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Common\Values\Root $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('Root');
        $visitor->setHeader('Content-Type', $generator->getMediaType('Root'));

        foreach ($data->getResources() as $resource) {
            if ($resource->mediaType === '') {
                $generator->startHashElement($resource->name);
                $generator->startAttribute('media-type', $resource->mediaType);
                $generator->endAttribute('media-type');
            } else {
                $generator->startObjectElement($resource->name, $resource->mediaType);
            }

            $generator->startAttribute('href', $resource->href);
            $generator->endAttribute('href');

            if ($resource->mediaType === '') {
                $generator->endHashElement($resource->name);
            } else {
                $generator->endObjectElement($resource->name);
            }
        }

        $generator->endObjectElement('Root');
    }
}
