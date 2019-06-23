<?php

/**
 * File containing the RestUserGroup ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
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
            $this->router->generate('ezpublish_rest_loadUserGroup', ['groupPath' => $mainLocationPath])
        );
        $generator->endAttribute('href');

        $generator->startAttribute('id', $contentInfo->id);
        $generator->endAttribute('id');

        $generator->startAttribute('remoteId', $contentInfo->remoteId);
        $generator->endAttribute('remoteId');

        $visitor->setHeader('Content-Type', $generator->getMediaType('UserGroup'));
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('UserGroupUpdate'));

        $generator->startObjectElement('ContentType');

        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_loadContentType',
                ['contentTypeId' => $contentInfo->contentTypeId]
            )
        );
        $generator->endAttribute('href');

        $generator->endObjectElement('ContentType');

        $generator->startValueElement('name', $contentInfo->name);
        $generator->endValueElement('name');

        $generator->startObjectElement('Versions', 'VersionList');
        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadContentVersions', ['contentId' => $contentInfo->id])
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Versions');

        $generator->startObjectElement('Section');
        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadSection', ['sectionId' => $contentInfo->sectionId])
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Section');

        $generator->startObjectElement('MainLocation', 'Location');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_loadLocation',
                ['locationPath' => $mainLocationPath]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('MainLocation');

        $generator->startObjectElement('Locations', 'LocationList');
        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadLocationsForContent', ['contentId' => $contentInfo->id])
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Locations');

        $generator->startObjectElement('Owner', 'User');
        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadUser', ['userId' => $contentInfo->ownerId])
        );
        $generator->endAttribute('href');
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
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_loadUserGroup',
                [
                    'groupPath' => implode('/', array_slice($mainLocation->path, 0, count($mainLocation->path) - 1)),
                ]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('ParentUserGroup');

        $generator->startObjectElement('Subgroups', 'UserGroupList');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_loadSubUserGroups',
                [
                    'groupPath' => $mainLocationPath,
                ]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Subgroups');

        $generator->startObjectElement('Users', 'UserList');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_loadUsersFromGroup',
                [
                    'groupPath' => $mainLocationPath,
                ]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Users');

        $generator->startObjectElement('Roles', 'RoleAssignmentList');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_loadRoleAssignmentsForUserGroup',
                [
                    'groupPath' => $mainLocationPath,
                ]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Roles');

        $generator->endObjectElement('UserGroup');
    }
}
