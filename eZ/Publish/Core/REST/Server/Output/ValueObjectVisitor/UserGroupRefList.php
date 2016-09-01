<?php

/**
 * File containing the UserGroupRefList ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Server\Values\ResourceRouteReference;

/**
 * UserGroupRefList value object visitor.
 */
class UserGroupRefList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
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

            $visitor->visitValueObject(
                new ResourceRouteReference(
                    'ezpublish_rest_loadUserGroup',
                    ['groupPath' => trim($userGroup->mainLocation->pathString, '/')]
                )
            );

            if ($data->userId !== null && $groupCount > 1) {
                $generator->startHashElement('unassign');

                $visitor->visitValueObject(
                    new ResourceRouteReference(
                        'ezpublish_rest_unassignUserFromUserGroup',
                        [
                            'userId' => $data->userId,
                            'groupPath' => $userGroup->mainLocation->path[count($userGroup->mainLocation->path) - 1],
                        ]
                    )
                );

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
