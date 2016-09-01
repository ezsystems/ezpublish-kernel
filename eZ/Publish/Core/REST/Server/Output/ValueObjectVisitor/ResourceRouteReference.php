<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Server\Values\ResourceLink as ResourceLinkValue;

class ResourceRouteReference extends ResourceLink
{
    /**
     * @param Visitor $visitor
     * @param Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\ResourceRouteReference $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        parent::visit(
            $visitor,
            $generator,
            new ResourceLinkValue(
                $this->router->generate(
                    $data->route,
                    $data->loadParameters
                ),
                isset($data->mediaTypeName) ? $generator->getMediaType($data->mediaTypeName) : null
            )
        );
    }
}
