<?php

/**
 * File containing the CreatedUserGroup ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * CreatedUserGroup value object visitor.
 *
 * @todo coverage add unit test
 */
class CreatedUserGroup extends RestUserGroup
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\CreatedUserGroup $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        parent::visit($visitor, $generator, $data->userGroup);
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ezpublish_rest_loadUserGroup',
                ['groupPath' => trim($data->userGroup->mainLocation->pathString, '/')]
            )
        );
        $visitor->setStatus(201);
    }
}
