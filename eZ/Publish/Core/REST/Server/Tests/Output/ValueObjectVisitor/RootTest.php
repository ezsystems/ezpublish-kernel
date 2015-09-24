<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Values\Root;

class RootTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the Role visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $role = new Root();

        $this->addRouteExpectation(
            'ezpublish_rest_createContent',
            array(),
            '/content/objects'
        );
        $this->addTemplatedRouteExpectation(
            'ezpublish_rest_redirectContent',
            array('remoteId' => '{remoteId}'),
            '/content/objects'
        );
        $this->addRouteExpectation('ezpublish_rest_listContentTypes', array(), '/content/types');
        $this->addTemplatedRouteExpectation(
            'ezpublish_rest_listContentTypes',
            array('identifier' => '{identifier}'),
            '/content/types?{&identifier}'
        );
        $this->addRouteExpectation('ezpublish_rest_createContentTypeGroup', array(), '/content/typegroups');
        $this->addTemplatedRouteExpectation(
            'ezpublish_rest_loadContentTypeGroupList',
            array('identifier' => '{identifier}'),
            '/content/typegroups?{&identifier}'
        );
        $this->addRouteExpectation('ezpublish_rest_loadUsers', array(), '/user/users');
        $this->addTemplatedRouteExpectation('ezpublish_rest_loadUsers', array('roleId' => '{roleId}'), '/user/users{?roleId}');
        $this->addTemplatedRouteExpectation('ezpublish_rest_loadUsers', array('remoteId' => '{remoteId}'), '/user/users{?remoteId}');
        $this->addTemplatedRouteExpectation('ezpublish_rest_loadUsers', array('email' => '{email}'), '/user/users{?email}');
        $this->addTemplatedRouteExpectation('ezpublish_rest_loadUsers', array('login' => '{login}'), '/user/users{?login}');

        $this->addRouteExpectation('ezpublish_rest_listRoles', array(), '/user/roles');
        $this->addRouteExpectation(
            'ezpublish_rest_loadLocation',
            array('locationPath' => '1/2'),
            '/content/locations/1/2'
        );
        $this->addRouteExpectation(
            'ezpublish_rest_loadUserGroup',
            array('groupPath' => '1/5'),
            '/user/groups/1/5'
        );
        $this->addRouteExpectation(
            'ezpublish_rest_loadLocation',
            array('locationPath' => '1/43'),
            '/content/locations/1/43'
        );
        $this->addTemplatedRouteExpectation(
            'ezpublish_rest_redirectLocation',
            array('remoteId' => '{remoteId}'),
            '/content/locations?{&remoteId}'
        );
        $this->addTemplatedRouteExpectation(
            'ezpublish_rest_redirectLocation',
            array('locationPath' => '{locationPath}'),
            '/content/locations?{&locationPath}'
        );
        $this->addRouteExpectation('ezpublish_rest_loadTrashItems', array(), '/content/trash');
        $this->addRouteExpectation('ezpublish_rest_listSections', array(), '/content/sections');
        $this->addRouteExpectation('ezpublish_rest_views_create', array(), '/views');
        $this->addRouteExpectation('ezpublish_rest_loadObjectStateGroups', array(), '/content/objectstategroups');
        $this->addTemplatedRouteExpectation(
            'ezpublish_rest_loadObjectStates',
            array('objectStateGroupId' => '{objectStateGroupId}'),
            '/content/objectstategroups/{objectStateGroupId}/objectstates'
        );
        $this->addRouteExpectation('ezpublish_rest_listGlobalURLAliases', array(), '/content/urlaliases');
        $this->addRouteExpectation('ezpublish_rest_listURLWildcards', array(), '/content/urlwildcards');
        $this->addRouteExpectation('ezpublish_rest_createSession', array(), '/user/sessions');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $role
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRootElement($result)
    {
        $this->assertXMLTag(
            array('tag' => 'Root'),
            $result,
            'Invalid <Root> element.',
            false
        );
    }

    /**
     * Test if result contains Role element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRootAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'Root',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Root+xml',
                ),
            ),
            $result,
            'Invalid <Root> attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'content',
            ),
            $result,
            'Invalid <content> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'content',
                'attributes' => array(
                    'media-type' => '',
                    'href' => '/content/objects',
                ),
            ),
            $result,
            'Invalid <content> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentByRemoteIdTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'contentByRemoteId',
            ),
            $result,
            'Missing <contentByRemoteId> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentByRemoteIdTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'contentByRemoteId',
                'attributes' => array(
                    'media-type' => '',
                    'href' => '/content/objects',
                ),
            ),
            $result,
            'Invalid <contentByRemoteId> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentTypesTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'contentTypes',
            ),
            $result,
            'Invalid <contentTypes> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentTypesTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'contentTypes',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ContentTypeInfoList+xml',
                    'href' => '/content/types',
                ),
            ),
            $result,
            'Invalid <content> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentTypeByIdentifierTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'contentTypeByIdentifier',
            ),
            $result,
            'Invalid <contentTypeByIdentifier> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentTypeByIdentifierTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'contentTypeByIdentifier',
                'attributes' => array(
                    'media-type' => '',
                    'href' => '/content/types?{&identifier}',
                ),
            ),
            $result,
            'Invalid <contentTypeByIdentifier> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentTypeGroupsTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'contentTypeGroups',
            ),
            $result,
            'Missing <contentTypeGroups> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentTypeGroupsTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'contentTypeGroups',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ContentTypeGroupList+xml',
                    'href' => '/content/typegroups',
                ),
            ),
            $result,
            'Invalid <contentTypeGroups> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentTypeGroupByIdentifierTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'contentTypeGroupByIdentifier',
            ),
            $result,
            'Missing <ContentTypeGroupByIdentifier> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentTypeGroupByIdentifierTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'contentTypeGroupByIdentifier',
                'attributes' => array(
                    'media-type' => '',
                    'href' => '/content/typegroups?{&identifier}',
                ),
            ),
            $result,
            'Invalid <contentTypeGroupByIdentifier> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsUsersTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'users',
            ),
            $result,
            'Invalid <users> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsUsersTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'users',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UserRefList+xml',
                    'href' => '/user/users',
                ),
            ),
            $result,
            'Invalid <users> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRolesTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'roles',
            ),
            $result,
            'Invalid <contentTypes> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRolesTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'roles',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.RoleList+xml',
                    'href' => '/user/roles',
                ),
            ),
            $result,
            'Invalid <roles> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRootLocationTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'rootLocation',
            ),
            $result,
            'Invalid <rootLocation> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRootLocationTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'rootLocation',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Location+xml',
                    'href' => '/content/locations/1/2',
                ),
            ),
            $result,
            'Invalid <rootLocation> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRootUserGroupTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'rootUserGroup',
            ),
            $result,
            'Invalid <rootUserGroup> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRootUserGroupTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'rootUserGroup',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UserGroup+xml',
                    'href' => '/user/groups/1/5',
                ),
            ),
            $result,
            'Invalid <rootUserGroup> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRootMediaFolderTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'rootMediaFolder',
            ),
            $result,
            'Invalid <rootMediaFolder> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRootMediaFolderTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'rootMediaFolder',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Location+xml',
                    'href' => '/content/locations/1/43',
                ),
            ),
            $result,
            'Invalid <rootMediaFolder> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsLocationByRemoteIdTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'locationByRemoteId',
            ),
            $result,
            'Missing <locationByRemoteId> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsLocationByRemoteIdTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'locationByRemoteId',
                'attributes' => array(
                    'media-type' => '',
                    'href' => '/content/locations?{&remoteId}',
                ),
            ),
            $result,
            'Invalid <locationByRemoteId> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsLocationByPathTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'locationByPath',
            ),
            $result,
            'Missing <locationByPath> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsLocationByPathTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'locationByPath',
                'attributes' => array(
                    'media-type' => '',
                    'href' => '/content/locations?{&locationPath}',
                ),
            ),
            $result,
            'Invalid <locationByPath> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsTrashTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'trash',
            ),
            $result,
            'Invalid <trash> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsTrashTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'trash',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Trash+xml',
                    'href' => '/content/trash',
                ),
            ),
            $result,
            'Invalid <trash> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsSectionsTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'sections',
            ),
            $result,
            'Invalid <sections> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsSectionTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'sections',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.SectionList+xml',
                    'href' => '/content/sections',
                ),
            ),
            $result,
            'Invalid <sections> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsViewsTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'views',
            ),
            $result,
            'Invalid <views> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsViewsTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'views',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.RefList+xml',
                    'href' => '/views',
                ),
            ),
            $result,
            'Invalid <views> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsObjectStateGroupsTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'objectStateGroups',
            ),
            $result,
            'Missing <objectStateGroups> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsObjectStateGroupsTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'objectStateGroups',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ObjectStateGroupList+xml',
                    'href' => '/content/objectstategroups',
                ),
            ),
            $result,
            'Invalid <objectStateGroups> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsObjectStatesTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'objectStates',
            ),
            $result,
            'Missing <objectStates> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsObjectStatesTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'objectStates',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ObjectStateList+xml',
                    'href' => '/content/objectstategroups/{objectStateGroupId}/objectstates',
                ),
            ),
            $result,
            'Invalid <objectStates> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsGlobalUrlAliasesTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'globalUrlAliases',
            ),
            $result,
            'Missing <globalUrlAliases> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsGlobalUrlAliasesTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'globalUrlAliases',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UrlAliasRefList+xml',
                    'href' => '/content/urlaliases',
                ),
            ),
            $result,
            'Invalid <globalUrlAliases> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsUrlWildcardsTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'urlWildcards',
            ),
            $result,
            'Missing <urlWildcards> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsUrlWildcardsTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'urlWildcards',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UrlWildcardList+xml',
                    'href' => '/content/urlwildcards',
                ),
            ),
            $result,
            'Invalid <globalUrlAliases> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsCreateSessionTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'createSession',
            ),
            $result,
            'Missing <createSession> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsCreateSessionTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'createSession',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UserSession+xml',
                    'href' => '/user/sessions',
                ),
            ),
            $result,
            'Invalid <createSession> tag attributes.',
            false
        );
    }

    /**
     * Get the Role visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\Root
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\Root();
    }
}
