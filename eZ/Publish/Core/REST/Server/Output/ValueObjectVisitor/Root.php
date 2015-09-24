<?php

/**
 * File containing the Root ValueObjectVisitor class.
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

/**
 * Root value object visitor.
 */
class Root extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Common\Values\Root $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('Root');
        $visitor->setHeader('Content-Type', $generator->getMediaType('Root'));

        // Uses hashElement instead of objectElement as a workaround to be able to have
        // an empty media-type, since there is no media type for list of content yet
        $generator->startHashElement('content');
        $generator->startAttribute('media-type', '');
        $generator->endAttribute('media-type');
        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_createContent')
        );
        $generator->endAttribute('href');
        $generator->endHashElement('content');

        // Uses hashElement instead of objectElement as a workaround to be able to have
        // an empty media-type, since there is no media type for list of content yet
        $generator->startHashElement('contentByRemoteId');
        $generator->startAttribute('media-type', '');
        $generator->endAttribute('media-type');
        $generator->startAttribute(
            'href',
            $this->templateRouter->generate(
                'ezpublish_rest_redirectContent',
                array('remoteId' => '{remoteId}')
            )
        );
        $generator->endAttribute('href');
        $generator->endHashElement('contentByRemoteId');

        // Content types list
        $generator->startObjectElement('contentTypes', 'ContentTypeInfoList');
        $generator->startAttribute('href', $this->router->generate('ezpublish_rest_listContentTypes'));
        $generator->endAttribute('href');
        $generator->endObjectElement('contentTypes');

        // Content type by identifier
        // See comment above regarding usage of hash element
        $generator->startHashElement('contentTypeByIdentifier');
        $generator->startAttribute('media-type', '');
        $generator->endAttribute('media-type');
        $generator->startAttribute(
            'href',
            $this->templateRouter->generate(
                'ezpublish_rest_listContentTypes',
                array('identifier' => '{identifier}')
            )
        );
        $generator->endAttribute('href');
        $generator->endHashElement('contentTypeByIdentifier');

        // Creating a ContentTypeGroup
        $generator->startObjectElement('contentTypeGroups', 'ContentTypeGroupList');
        $generator->startAttribute('href', $this->router->generate('ezpublish_rest_createContentTypeGroup'));
        $generator->endAttribute('href');
        $generator->endObjectElement('contentTypeGroups');

        // Content type group by identifier
        // See comment above regarding usage of hash element
        $generator->startHashElement('contentTypeGroupByIdentifier');
        $generator->startAttribute('media-type', '');
        $generator->endAttribute('media-type');
        $generator->startAttribute(
            'href',
            $this->templateRouter->generate(
                'ezpublish_rest_loadContentTypeGroupList',
                array('identifier' => '{identifier}')
            )
        );
        $generator->endAttribute('href');
        $generator->endHashElement('contentTypeGroupByIdentifier');

        // Users
        $generator->startObjectElement('users', 'UserRefList');
        $generator->startAttribute('href', $this->router->generate('ezpublish_rest_loadUsers'));
        $generator->endAttribute('href');
        $generator->endObjectElement('users');

        // Users by role id
        $generator->startObjectElement('usersByRoleId', 'UserRefList');
        $generator->startAttribute('href', $this->templateRouter->generate('ezpublish_rest_loadUsers', ['roleId' => '{roleId}']));
        $generator->endAttribute('href');
        $generator->endObjectElement('usersByRoleId');

        // Users by remote id
        $generator->startObjectElement('usersByRemoteId', 'UserRefList');
        $generator->startAttribute('href', $this->templateRouter->generate('ezpublish_rest_loadUsers', ['remoteId' => '{remoteId}']));
        $generator->endAttribute('href');
        $generator->endObjectElement('usersByRemoteId');

        // Users by email
        $generator->startObjectElement('usersByEmail', 'UserRefList');
        $generator->startAttribute('href', $this->templateRouter->generate('ezpublish_rest_loadUsers', ['email' => '{email}']));
        $generator->endAttribute('href');
        $generator->endObjectElement('usersByEmail');

        // Users by login
        $generator->startObjectElement('usersByLogin', 'UserRefList');
        $generator->startAttribute('href', $this->templateRouter->generate('ezpublish_rest_loadUsers', ['login' => '{login}']));
        $generator->endAttribute('href');
        $generator->endObjectElement('usersByLogin');

        // Roles
        $generator->startObjectElement('roles', 'RoleList');
        $generator->startAttribute('href', $this->router->generate('ezpublish_rest_listRoles'));
        $generator->endAttribute('href');
        $generator->endObjectElement('roles');

        // Content root location
        $generator->startObjectElement('rootLocation', 'Location');
        $generator->startAttribute('href', $this->router->generate('ezpublish_rest_loadLocation', array('locationPath' => '1/2')));
        $generator->endAttribute('href');
        $generator->endObjectElement('rootLocation');

        // Users root location
        $generator->startObjectElement('rootUserGroup', 'UserGroup');
        $generator->startAttribute('href', $this->router->generate('ezpublish_rest_loadUserGroup', array('groupPath' => '1/5')));
        $generator->endAttribute('href');
        $generator->endObjectElement('rootUserGroup');

        // Media root location
        $generator->startObjectElement('rootMediaFolder', 'Location');
        $generator->startAttribute('href', $this->router->generate('ezpublish_rest_loadLocation', array('locationPath' => '1/43')));
        $generator->endAttribute('href');
        $generator->endObjectElement('rootMediaFolder');

        // Location by remote ID
        // See comment above regarding usage of hash element
        $generator->startHashElement('locationByRemoteId');
        $generator->startAttribute('media-type', '');
        $generator->endAttribute('media-type');
        $generator->startAttribute(
            'href',
            $this->templateRouter->generate(
                'ezpublish_rest_redirectLocation',
                array('remoteId' => '{remoteId}')
            )
        );
        $generator->endAttribute('href');
        $generator->endHashElement('locationByRemoteId');

        $generator->startHashElement('locationByPath');
        $generator->startAttribute('media-type', '');
        $generator->endAttribute('media-type');
        $generator->startAttribute(
            'href',
            $this->templateRouter->generate(
                'ezpublish_rest_redirectLocation',
                array('locationPath' => '{locationPath}')
            )
        );
        $generator->endAttribute('href');
        $generator->endHashElement('locationByPath');

        // Trash
        $generator->startObjectElement('trash', 'Trash');
        $generator->startAttribute('href', $this->router->generate('ezpublish_rest_loadTrashItems'));
        $generator->endAttribute('href');
        $generator->endObjectElement('trash');

        // Sections list
        $generator->startObjectElement('sections', 'SectionList');
        $generator->startAttribute('href', $this->router->generate('ezpublish_rest_listSections'));
        $generator->endAttribute('href');
        $generator->endObjectElement('sections');

        // Content views
        $generator->startObjectElement('views', 'RefList');
        $generator->startAttribute('href', $this->router->generate('ezpublish_rest_views_create'));
        $generator->endAttribute('href');
        $generator->endObjectElement('views');

        // Object states groups
        $generator->startObjectElement('objectStateGroups', 'ObjectStateGroupList');
        $generator->startAttribute('href', $this->router->generate('ezpublish_rest_loadObjectStateGroups'));
        $generator->endAttribute('href');
        $generator->endObjectElement('objectStateGroups');

        // Object states
        $generator->startObjectElement('objectStates', 'ObjectStateList');
        $generator->startAttribute(
            'href',
            $this->templateRouter->generate(
                'ezpublish_rest_loadObjectStates',
                array('objectStateGroupId' => '{objectStateGroupId}')
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('objectStates');

        // Global URL aliases
        $generator->startObjectElement('globalUrlAliases', 'UrlAliasRefList');
        $generator->startAttribute('href', $this->router->generate('ezpublish_rest_listGlobalURLAliases'));
        $generator->endAttribute('href');
        $generator->endObjectElement('globalUrlAliases');

        // URL wildcards
        $generator->startObjectElement('urlWildcards', 'UrlWildcardList');
        $generator->startAttribute('href', $this->router->generate('ezpublish_rest_listURLWildcards'));
        $generator->endAttribute('href');
        $generator->endObjectElement('urlWildcards');

        // Create session
        $generator->startObjectElement('createSession', 'UserSession');
        $generator->startAttribute('href', $this->router->generate('ezpublish_rest_createSession'));
        $generator->endAttribute('href');
        $generator->endObjectElement('createSession');

        $generator->startObjectElement('refreshSession', 'UserSession');
        $generator->startAttribute(
            'href',
            $this->templateRouter->generate(
                'ezpublish_rest_refreshSession',
                array('sessionId' => '{sessionId}')
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('refreshSession');

        $generator->endObjectElement('Root');
    }
}
