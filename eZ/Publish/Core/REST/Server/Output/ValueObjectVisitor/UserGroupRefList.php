<?php

/**
 * File containing the UserGroupRefList ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRestCommon\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRestCommon\Output\Generator;
use EzSystems\EzPlatformRestCommon\Output\Visitor;

/**
 * UserGroupRefList value object visitor.
 */
class UserGroupRefList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRestCommon\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRestCommon\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\UserGroupRefList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('UserGroupRefList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('UserGroupRefList'));
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader('Accept-Patch', false);

        $generator->startAttribute('href', $data->path);
        $generator->endAttribute('href');

        $groupCount = count($data->userGroups);

        $generator->startList('UserGroup');
        foreach ($data->userGroups as $userGroup) {
            $generator->startObjectElement('UserGroup');

            $generator->startAttribute(
                'href',
                $this->router->generate(
                    'ezpublish_rest_loadUserGroup',
                    array(
                        'groupPath' => trim($userGroup->mainLocation->pathString, '/'),
                    )
                )
            );
            $generator->endAttribute('href');

            if ($data->userId !== null && $groupCount > 1) {
                $generator->startHashElement('unassign');

                $generator->startAttribute(
                    'href',
                    $this->router->generate(
                        'ezpublish_rest_unassignUserFromUserGroup',
                        array(
                            'userId' => $data->userId,
                            'groupPath' => $userGroup->mainLocation->path[count($userGroup->mainLocation->path) - 1],
                        )
                    )
                );
                $generator->endAttribute('href');

                $generator->startAttribute('method', 'DELETE');
                $generator->endAttribute('method');

                $generator->endHashElement('unassign');
            }

            $generator->endObjectElement('UserGroup');
        }
        $generator->endList('UserGroup');

        $generator->endObjectElement('UserGroupRefList');
    }
}
