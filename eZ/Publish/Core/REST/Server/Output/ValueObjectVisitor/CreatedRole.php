<?php

/**
 * File containing the CreatedRole ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRest\Output\Generator;
use EzSystems\EzPlatformRest\Output\Visitor;

/**
 * CreatedRole value object visitor.
 *
 * @todo coverage add unit test
 */
class CreatedRole extends Role
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRest\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRest\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\CreatedRole $data
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
        $visitor->setStatus(201);
    }
}
