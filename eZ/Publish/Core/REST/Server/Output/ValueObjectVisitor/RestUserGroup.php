<?php

/**
 * File containing the RestUserGroup ValueObjectVisitor class.
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
use eZ\Publish\Core\REST\Server\Values\Version as VersionValue;

/**
 * RestUserGroup value object visitor.
 */
class RestUserGroup extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\RestUserGroup $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $contentInfo = $data->contentInfo;
        $mainLocation = $data->mainLocation;
        $mainLocationPath = trim($mainLocation->pathString, '/');

        $generator->startObjectElement('UserGroup');

        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadUserGroup', array('groupPath' => $mainLocationPath))
        );
        $generator->endAttribute('href');

        $generator->startAttribute('id', $contentInfo->id);
        $generator->endAttribute('id');

        $generator->startAttribute('remoteId', $contentInfo->remoteId);
        $generator->endAttribute('remoteId');

        $visitor->setHeader('Content-Type', $generator->getMediaType('UserGroup'));
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('UserGroupUpdate'));

        $generator->startObjectElement('ContentType');

        $visitor->visitValueObject(
            new ResourceRouteReference(
                'ezpublish_rest_loadContentType',
                ['contentTypeId' => $contentInfo->contentTypeId]
            ),
            $generator,
            $visitor
        );

        $generator->endObjectElement('ContentType');

        $generator->startValueElement('name', $contentInfo->name);
        $generator->endValueElement('name');

        $generator->startObjectElement('Versions', 'VersionList');

        $visitor->visitValueObject(
            new ResourceRouteReference('ezpublish_rest_loadContentVersions', ['contentId' => $contentInfo->id]),
            $generator,
            $visitor
        );
        $generator->endObjectElement('Versions');

        $generator->startObjectElement('Section');
        $visitor->visitValueObject(
            new ResourceRouteReference('ezpublish_rest_loadSection', array('sectionId' => $contentInfo->sectionId)),
            $generator,
            $visitor
        );
        $generator->endObjectElement('Section');

        $generator->startObjectElement('MainLocation', 'Location');
        $visitor->visitValueObject(
            new ResourceRouteReference('ezpublish_rest_loadLocation', ['locationPath' => $mainLocationPath]),
            $generator,
            $visitor
        );
        $generator->endObjectElement('MainLocation');

        $generator->startObjectElement('Locations', 'LocationList');
        $visitor->visitValueObject(
            new ResourceRouteReference('ezpublish_rest_loadLocationsForContent', ['contentId' => $contentInfo->id]),
            $generator,
            $visitor
        );
        $generator->endObjectElement('Locations');

        $generator->startObjectElement('Owner', 'User');
        $visitor->visitValueObject(
            new ResourceRouteReference('ezpublish_rest_loadUser', ['userId' => $contentInfo->ownerId]),
            $generator,
            $visitor
        );
        $generator->endObjectElement('Owner');

        $generator->startValueElement('publishDate', $contentInfo->publishedDate->format('c'));
        $generator->endValueElement('publishDate');

        $generator->startValueElement('lastModificationDate', $contentInfo->modificationDate->format('c'));
        $generator->endValueElement('lastModificationDate');

        $generator->startValueElement('mainLanguageCode', $contentInfo->mainLanguageCode);
        $generator->endValueElement('mainLanguageCode');

        $generator->startValueElement(
            'alwaysAvailable',
            $this->serializeBool($generator, $contentInfo->alwaysAvailable)
        );
        $generator->endValueElement('alwaysAvailable');

        $visitor->visitValueObject(
            new VersionValue(
                $data->content,
                $data->contentType,
                $data->relations
            )
        );

        $generator->startObjectElement('ParentUserGroup', 'UserGroup');
        $visitor->visitValueObject(
            new ResourceRouteReference(
                'ezpublish_rest_loadUserGroup',
                ['groupPath' => implode('/', array_slice($mainLocation->path, 0, count($mainLocation->path) - 1))]
            ),
            $generator,
            $visitor
        );
        $generator->endObjectElement('ParentUserGroup');

        $generator->startObjectElement('Subgroups', 'UserGroupList');
        $visitor->visitValueObject(
            new ResourceRouteReference('ezpublish_rest_loadSubUserGroups', ['groupPath' => $mainLocationPath]),
            $generator,
            $visitor
        );
        $generator->endObjectElement('Subgroups');

        $generator->startObjectElement('Users', 'UserList');
        $visitor->visitValueObject(
            new ResourceRouteReference('ezpublish_rest_loadUsersFromGroup', ['groupPath' => $mainLocationPath]),
            $generator,
            $visitor
        );
        $generator->endObjectElement('Users');

        $generator->startObjectElement('Roles', 'RoleAssignmentList');
        $visitor->visitValueObject(
            new ResourceRouteReference('ezpublish_rest_loadRoleAssignmentsForUserGroup', ['groupPath' => $mainLocationPath]),
            $generator,
            $visitor
        );
        $generator->endObjectElement('Roles');

        $generator->endObjectElement('UserGroup');
    }
}
