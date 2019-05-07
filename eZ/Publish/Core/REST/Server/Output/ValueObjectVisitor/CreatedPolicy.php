<?php

/**
 * File containing the CreatedPolicy ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRestCommon\Output\Generator;
use EzSystems\EzPlatformRestCommon\Output\Visitor;

/**
 * CreatedPolicy value object visitor.
 *
 * @todo coverage add unit test
 */
class CreatedPolicy extends Policy
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRestCommon\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRestCommon\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\CreatedPolicy $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        parent::visit($visitor, $generator, $data->policy);
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ezpublish_rest_loadPolicy',
                array(
                    'roleId' => $data->policy->roleId,
                    'policyId' => $data->policy->id,
                )
            )
        );
        $visitor->setStatus(201);
    }
}
