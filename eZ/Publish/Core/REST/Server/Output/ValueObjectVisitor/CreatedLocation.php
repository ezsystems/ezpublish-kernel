<?php

/**
 * File containing the CreatedLocation ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRestCommon\Output\Generator;
use EzSystems\EzPlatformRestCommon\Output\Visitor;

/**
 * CreatedLocation value object visitor.
 *
 * @todo coverage add test
 */
class CreatedLocation extends RestLocation
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRestCommon\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRestCommon\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\CreatedLocation $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        parent::visit($visitor, $generator, $data->restLocation);
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ezpublish_rest_loadLocation',
                array('locationPath' => trim($data->restLocation->location->pathString, '/'))
            )
        );
        $visitor->setStatus(201);
    }
}
