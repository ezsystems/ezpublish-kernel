<?php

/**
 * File containing the RestUser ValueObjectVisitor class.
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
 * RestUser value object visitor.
 */
class RestUser extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\RestUser $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $contentInfo = $data->contentInfo;

        $generator->startObjectElement('User');

        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadUser', array('userId' => $contentInfo->id))
        );
        $generator->endAttribute('href');

        $generator->startAttribute('id', $contentInfo->id);
        $generator->endAttribute('id');

        $generator->startAttribute('remoteId', $contentInfo->remoteId);
        $generator->endAttribute('remoteId');

        $visitor->setHeader('Content-Type', $generator->getMediaType('User'));
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('UserUpdate'));

        $generator->startObjectElement('ContentType');

        $visitor->visitValueObject(
            new ResourceRouteReference(
                'ezpublish_rest_loadContentType',
                ['contentTypeId' => $contentInfo->contentTypeId]
            )
        );
        $generator->endObjectElement('ContentType');

        $generator->startValueElement('name', $contentInfo->name);
        $generator->endValueElement('name');

        $generator->startObjectElement('Versions', 'VersionList');
        $visitor->visitValueObject(
            new ResourceRouteReference(
                'ezpublish_rest_loadContentVersions',
                ['contentId' => $contentInfo->id]
            )
        );
        $generator->endObjectElement('Versions');

        $generator->startObjectElement('Section');
        $visitor->visitValueObject(
            new ResourceRouteReference(
                'ezpublish_rest_loadSection',
                ['sectionId' => $contentInfo->sectionId]
            )
        );
        $generator->endObjectElement('Section');

        $generator->startObjectElement('MainLocation', 'Location');
        $visitor->visitValueObject(
            new ResourceRouteReference(
                'ezpublish_rest_loadLocation',
                ['locationPath' => trim($data->mainLocation->pathString, '/')]
            )
        );
        $generator->endObjectElement('MainLocation');

        $generator->startObjectElement('Locations', 'LocationList');
        $visitor->visitValueObject(
            new ResourceRouteReference(
                'ezpublish_rest_loadLocationsForContent',
                ['contentId' => $contentInfo->id]
            )
        );
        $generator->endObjectElement('Locations');

        $generator->startObjectElement('Groups', 'UserGroupRefList');
        $visitor->visitValueObject(
            new ResourceRouteReference(
                'ezpublish_rest_loadUserGroupsOfUser',
                ['userId' => $contentInfo->id]
            )
        );
        $generator->endObjectElement('Groups');

        $generator->startObjectElement('Owner', 'User');
        $visitor->visitValueObject(
            new ResourceRouteReference(
                'ezpublish_rest_loadUser',
                ['userId' => $contentInfo->ownerId]
            )
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

        $generator->startValueElement('login', $data->content->login);
        $generator->endValueElement('login');

        $generator->startValueElement('email', $data->content->email);
        $generator->endValueElement('email');

        $generator->startValueElement(
            'enabled',
            $this->serializeBool($generator, $data->content->enabled)
        );
        $generator->endValueElement('enabled');

        $generator->startObjectElement('UserGroups', 'UserGroupList');
        $visitor->visitValueObject(
            new ResourceRouteReference('ezpublish_rest_loadUserGroupsOfUser', ['userId' => $contentInfo->id])
        );
        $generator->endObjectElement('UserGroups');

        $generator->startObjectElement('Roles', 'RoleAssignmentList');
        $visitor->visitValueObject(
            new ResourceRouteReference('ezpublish_rest_loadRoleAssignmentsForUser', ['userId' => $contentInfo->id])
        );
        $generator->endObjectElement('Roles');

        $generator->endObjectElement('User');
    }
}
