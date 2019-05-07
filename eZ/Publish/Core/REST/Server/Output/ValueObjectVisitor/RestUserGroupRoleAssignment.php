<?php

/**
 * File containing the RestUserGroupRoleAssignment ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRestCommon\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRestCommon\Output\Generator;
use EzSystems\EzPlatformRestCommon\Output\Visitor;
use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;

/**
 * RestUserGroupRoleAssignment value object visitor.
 */
class RestUserGroupRoleAssignment extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRestCommon\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRestCommon\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\RestUserGroupRoleAssignment $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('RoleAssignment');
        $visitor->setHeader('Content-Type', $generator->getMediaType('RoleAssignment'));

        $roleAssignment = $data->roleAssignment;
        $role = $roleAssignment->getRole();

        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_loadRoleAssignmentForUserGroup',
                array(
                    'groupPath' => trim($data->id, '/'),
                    'roleId' => $role->id,
                )
            )
        );
        $generator->endAttribute('href');

        $roleLimitation = $roleAssignment->getRoleLimitation();
        if ($roleLimitation instanceof RoleLimitation) {
            $this->visitLimitation($generator, $roleLimitation);
        }

        $generator->startObjectElement('Role');
        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadRole', array('roleId' => $role->id))
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Role');

        $generator->endObjectElement('RoleAssignment');
    }
}
