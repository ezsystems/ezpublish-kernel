<?php

/**
 * File containing the PublishedRole ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRestCommon\Output\Generator;
use EzSystems\EzPlatformRestCommon\Output\Visitor;

/**
 * PublishedRole value object visitor.
 *
 * @todo coverage add unit test
 */
class PublishedRole extends Role
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRestCommon\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRestCommon\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\PublishedRole $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        parent::visit($visitor, $generator, $data->role);
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ezpublish_rest_loadRole',
                array('roleId' => $data->role->id)
            )
        );
        $visitor->setStatus(204);
    }
}
