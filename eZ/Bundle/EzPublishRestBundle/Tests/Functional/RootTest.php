<?php

/**
 * File containing the Functional\RootTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;
use eZ\Publish\Core\REST\Common\Tests\AssertXmlTagTrait;

class RootTest extends RESTFunctionalTestCase
{
    use AssertXmlTagTrait;

    /**
     * @covers GET /
     */
    public function testLoadRootResource()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/')
        );
        self::assertHttpResponseCodeEquals($response, 200);

        return $response->getContent();
    }

    /**
     * @dataProvider getRandomUriSet
     * @covers GET /<wrongUri>
     */
    public function testCatchAll($uri)
    {
        self::markTestSkipped('@todo fixme');
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/' . uniqid('rest'), '', 'Stuff+json')
        );
        self::assertHttpResponseCodeEquals($response, 404);
        $responseArray = json_decode($response->getContent(), true);
        self::assertArrayHasKey('ErrorMessage', $responseArray);
        self::assertEquals('No such route', $responseArray['ErrorMessage']['errorDescription']);
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsContentTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'content',
                'attributes' => array(
                    'media-type' => '',
                    'href' => '/api/ezp/v2/content/objects',
                ),
            ),
            $result,
            'Invalid <content> element.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsContentByRemoteIdTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'contentByRemoteId',
                'attributes' => array(
                    'media-type' => '',
                    'href' => '/api/ezp/v2/content/objects{?remoteId}',
                ),
            ),
            $result,
            'Invalid <contentByRemoteId> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsContentTypesTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'contentTypes',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ContentTypeInfoList+xml',
                    'href' => '/api/ezp/v2/content/types',
                ),
            ),
            $result,
            'Invalid <content> element.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsContentTypeByIdentifierTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'contentTypeByIdentifier',
                'attributes' => array(
                    'media-type' => '',
                    'href' => '/api/ezp/v2/content/types{?identifier}',
                ),
            ),
            $result,
            'Invalid <contentTypeByIdentifier> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsContentTypeGroupsTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'contentTypeGroups',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ContentTypeGroupList+xml',
                    'href' => '/api/ezp/v2/content/typegroups',
                ),
            ),
            $result,
            'Invalid <contentTypeGroups> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsContentTypeGroupByIdentifierTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'contentTypeGroupByIdentifier',
                'attributes' => array(
                    'media-type' => '',
                    'href' => '/api/ezp/v2/content/typegroups{?identifier}',
                ),
            ),
            $result,
            'Invalid <contentTypeGroupByIdentifier> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsUsersTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'users',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UserRefList+xml',
                    'href' => '/api/ezp/v2/user/users',
                ),
            ),
            $result,
            'Invalid <users> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsUsersByRoleIdentifierTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'usersByRoleId',
            ),
            $result,
            'Missing <usersByRoleId> element.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsUsersByRoleIdentifierTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'usersByRoleId',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UserRefList+xml',
                    'href' => '/api/ezp/v2/user/users{?roleId}',
                ),
            ),
            $result,
            'Invalid <usersByRoleId> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsUsersByRemoteIdentifierTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'usersByRemoteId',
            ),
            $result,
            'Missing <usersByRemoteId> element.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsUsersByRemoteIdentifierTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'usersByRemoteId',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UserRefList+xml',
                    'href' => '/api/ezp/v2/user/users{?remoteId}',
                ),
            ),
            $result,
            'Invalid <usersByRemoteId> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsUsersByEmailTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'usersByEmail',
            ),
            $result,
            'Missing <usersByEmail> element.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsUsersByEmailTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'usersByEmail',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UserRefList+xml',
                    'href' => '/api/ezp/v2/user/users{?email}',
                ),
            ),
            $result,
            'Invalid <usersByEmail> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsUsersByLoginTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'usersByLogin',
            ),
            $result,
            'Missing <usersByLogin> element.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsUsersByLoginTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'usersByLogin',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UserRefList+xml',
                    'href' => '/api/ezp/v2/user/users{?login}',
                ),
            ),
            $result,
            'Invalid <usersByLogin> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsRolesTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'roles',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.RoleList+xml',
                    'href' => '/api/ezp/v2/user/roles',
                ),
            ),
            $result,
            'Invalid <roles> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsRootLocationTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'rootLocation',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Location+xml',
                    'href' => '/api/ezp/v2/content/locations/1/2',
                ),
            ),
            $result,
            'Invalid <rootLocation> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsRootUserGroupTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'rootUserGroup',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UserGroup+xml',
                    'href' => '/api/ezp/v2/user/groups/1/5',
                ),
            ),
            $result,
            'Invalid <rootUserGroup> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsRootMediaFolderTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'rootMediaFolder',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Location+xml',
                    'href' => '/api/ezp/v2/content/locations/1/43',
                ),
            ),
            $result,
            'Invalid <rootMediaFolder> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsLocationByRemoteIdTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'locationByRemoteId',
                'attributes' => array(
                    'media-type' => '',
                    'href' => '/api/ezp/v2/content/locations{?remoteId}',
                ),
            ),
            $result,
            'Invalid <locationByRemoteId> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsLocationByPathTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'locationByPath',
                'attributes' => array(
                    'media-type' => '',
                    'href' => '/api/ezp/v2/content/locations{?locationPath}',
                ),
            ),
            $result,
            'Invalid <locationByPath> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsTrashTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'trash',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Trash+xml',
                    'href' => '/api/ezp/v2/content/trash',
                ),
            ),
            $result,
            'Invalid <trash> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsSectionTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'sections',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.SectionList+xml',
                    'href' => '/api/ezp/v2/content/sections',
                ),
            ),
            $result,
            'Invalid <sections> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsViewsTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'views',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.RefList+xml',
                    'href' => '/api/ezp/v2/views',
                ),
            ),
            $result,
            'Invalid <views> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsObjectStateGroupsTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'objectStateGroups',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ObjectStateGroupList+xml',
                    'href' => '/api/ezp/v2/content/objectstategroups',
                ),
            ),
            $result,
            'Invalid <objectStateGroups> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsObjectStatesTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'objectStates',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ObjectStateList+xml',
                    'href' => '/api/ezp/v2/content/objectstategroups/{objectStateGroupId}/objectstates',
                ),
            ),
            $result,
            'Invalid <objectStates> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsGlobalUrlAliasesTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'globalUrlAliases',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UrlAliasRefList+xml',
                    'href' => '/api/ezp/v2/content/urlaliases',
                ),
            ),
            $result,
            'Invalid <globalUrlAliases> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsUrlWildcardsTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'urlWildcards',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UrlWildcardList+xml',
                    'href' => '/api/ezp/v2/content/urlwildcards',
                ),
            ),
            $result,
            'Invalid <globalUrlAliases> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
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
     * @depends testLoadRootResource
     */
    public function testResultContainsCreateSessionTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'createSession',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UserSession+xml',
                    'href' => '/api/ezp/v2/user/sessions',
                ),
            ),
            $result,
            'Invalid <createSession> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsRefreshSessionTag($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'refreshSession',
            ),
            $result,
            'Missing <refreshSession> tag.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsRefreshSessionTagAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'refreshSession',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.UserSession+xml',
                    'href' => '/api/ezp/v2/user/sessions/{sessionId}/refresh',
                ),
            ),
            $result,
            'Invalid <refreshSession> tag attributes.',
            false
        );
    }

    public function getRandomUriSet()
    {
        return array(
            array('/api/ezp/v2/randomUri'),
            array('/api/ezp/v2/randomUri/level/two'),
            array('/api/ezp/v2/randomUri/with/arguments?arg=argh'),
        );
    }
}
