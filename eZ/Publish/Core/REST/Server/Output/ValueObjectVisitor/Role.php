<?php

/**
 * File containing the Role ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\API\Repository\Values\User\RoleDraft;
use EzSystems\EzPlatformRestCommon\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRestCommon\Output\Generator;
use EzSystems\EzPlatformRestCommon\Output\Visitor;

/**
 * Role value object visitor.
 */
class Role extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRestCommon\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRestCommon\Output\Generator $generator
     * @param Role|RoleDraft $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('Role');
        $visitor->setHeader('Content-Type', $generator->getMediaType($data instanceof RoleDraft ? 'RoleDraft' : 'Role'));
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('RoleInput'));
        $this->visitRoleAttributes($visitor, $generator, $data);
        $generator->endObjectElement('Role');
    }

    protected function visitRoleAttributes(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadRole', array('roleId' => $data->id))
        );
        $generator->endAttribute('href');

        $generator->startValueElement('identifier', $data->identifier);
        $generator->endValueElement('identifier');

        $generator->startObjectElement('Policies', 'PolicyList');
        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadPolicies', array('roleId' => $data->id))
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Policies');
    }
}
