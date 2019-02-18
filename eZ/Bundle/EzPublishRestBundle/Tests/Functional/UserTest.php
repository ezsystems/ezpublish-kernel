<?php

/**
 * File containing the Functional\UserTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;

class UserTest extends RESTFunctionalTestCase
{
    /**
     * Covers GET /user/groups/root.
     */
    public function loadRootUserGroup()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/user/groups/root')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers POST /user/groups/{groupPath}/subgroups.
     *
     * @return string the created user group href
     */
    public function testCreateUserGroup()
    {
        $text = $this->addTestSuffix(__FUNCTION__);
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<UserGroupCreate>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <remoteId>{$text}</remoteId>
  <fields>
    <field>
      <fieldDefinitionIdentifier>name</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>{$text}</fieldValue>
    </field>
    <field>
      <fieldDefinitionIdentifier>description</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>Description of {$text}</fieldValue>
    </field>
  </fields>
</UserGroupCreate>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/user/groups/1/5/subgroups',
            'UserGroupCreate+xml',
            'UserGroup+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @param $userGroupId
     * Covers GET /user/groups/{groupId}
     * @depends testCreateUserGroup
     */
    public function testLoadUserGroup($groupId)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $groupId)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers PATCH /user/groups/{groupPath}.
     * @depends testCreateUserGroup
     */
    public function testUpdateUserGroup($groupHref)
    {
        $text = $this->addTestSuffix(__FUNCTION__);
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<UserGroupUpdate>
  <fields>
    <field>
      <fieldDefinitionIdentifier>description</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>{$text}</fieldValue>
    </field>
  </fields>
</UserGroupUpdate>
XML;
        $request = $this->createHttpRequest(
            'PATCH',
            $groupHref,
            'UserGroupUpdate+xml',
            'UserGroup+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateUserGroup
     * Covers POST /user/groups/{groupPath}/users
     *
     * @return string The created user  href
     */
    public function testCreateUser($userGroupHref)
    {
        $text = $this->addTestSuffix(__FUNCTION__);
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<UserCreate>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <remoteId>{$text}</remoteId>
  <login>{$text}</login>
  <email>{$text}@example.net</email>
  <password>{$text}</password>
  <fields>
    <field>
      <fieldDefinitionIdentifier>first_name</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>John</fieldValue>
    </field>
    <field>
      <fieldDefinitionIdentifier>last_name</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>Doe</fieldValue>
    </field>
  </fields>
</UserCreate>
XML;
        $request = $this->createHttpRequest(
            'POST',
            "{$userGroupHref}/users",
            'UserCreate+xml',
            'User+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @param $userId
     * Covers GET /user/users/{userId}
     * @depends testCreateUser
     */
    public function testLoadUser($userHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $userHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateUser
     * Covers PATCH /user/users/{userId}
     */
    public function testUpdateUser($userHref)
    {
        $text = $this->addTestSuffix(__FUNCTION__);
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<UserUpdate>
  <fields>
    <field>
      <fieldDefinitionIdentifier>first_name</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>john john</fieldValue>
    </field>
  </fields>
</UserUpdate>
XML;
        $request = $this->createHttpRequest(
            'PATCH',
            $userHref,
            'UserUpdate+xml',
            'User+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /user/users.
     */
    public function testLoadUsers()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/user/users')
        );

        self::assertHttpResponseCodeEquals($response, 404);
    }

    /**
     * @depends testCreateUser
     * Covers GET /user/users?remoteId={userRemoteId}
     */
    public function testLoadUserByRemoteId()
    {
        $remoteId = $this->addTestSuffix('testCreateUser');
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "/api/ezp/v2/user/users?remoteId=$remoteId")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /user/groups.
     */
    public function testLoadUserGroups()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/user/groups')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateUserGroup
     * Covers GET /user/groups?remoteId={groupRemoteId}
     */
    public function testLoadUserGroupByRemoteId($groupHref)
    {
        $remoteId = $this->addTestSuffix('testCreateUserGroup');
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "/api/ezp/v2/user/groups?remoteId=$remoteId")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /user/users/{userId}/drafts.
     * @depends testCreateUser
     */
    public function testLoadUserDrafts($userHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$userHref/drafts")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateUserGroup
     * Covers GET /user/groups/{groupPath}/subgroups
     */
    public function testLoadSubUserGroups($groupHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$groupHref/subgroups")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /user/users/{userId}/groups.
     * @depends testCreateUser
     */
    public function testLoadUserGroupsOfUser($userHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$userHref/groups")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /user/groups/<groupPath>/users.
     * @depends testCreateUserGroup
     */
    public function testLoadUsersFromGroup($groupHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$groupHref/users")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers POST /user/users/{userId}/groups.
     * @depends testCreateUser
     *
     * @return string $userHref
     */
    public function testAssignUserToUserGroup($userHref)
    {
        // /1/5/12 is Members
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('POST', "$userHref/groups?group=/user/groups/1/5/12")
        );

        self::assertHttpResponseCodeEquals($response, 200);

        return $userHref;
    }

    /**
     * Covers DELETE /user/users/{userId}/groups/{groupPath}.
     * @depends testAssignUserToUserGroup
     */
    public function testUnassignUserFromUserGroup($userHref)
    {
        // /1/5/12 is Members
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', "$userHref/groups/12")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers MOVE /user/groups/{groupPath}.
     * @depends testCreateUserGroup
     */
    public function testMoveUserGroup($groupHref)
    {
        $request = $this->createHttpRequest(
            'MOVE',
            $groupHref,
            '',
            '',
            '',
            ['Destination' => '/api/ezp/v2/user/groups/1/5/12']
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
    }

    /**
     * @depends testCreateUser
     * Covers POST /user/sessions
     *
     * @return string The created session href
     */
    public function testCreateSession()
    {
        self::markTestSkipped('@todo fixme');

        $text = $this->addTestSuffix('testCreateUser');
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<SessionInput>
  <login>$text</login>
  <password>$text</password>
</SessionInput>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/user/sessions',
            'SessionInput+xml',
            'Session+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @depends testCreateSession
     * Covers DELETE /user/sessions/{sessionId}
     */
    public function testDeleteSession($sessionHref)
    {
        self::markTestSkipped('@todo improve. The session can only be deleted if started !');
        $response = $this->sendHttpRequest(
            $request = $this->createHttpRequest('DELETE', $sessionHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * @depends testCreateUser
     * Covers DELETE /user/users/{userId}
     */
    public function testDeleteUser($userHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $userHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * @depends testCreateUserGroup
     * Covers DELETE /user/users/{userId}
     */
    public function testDeleteUserGroup($groupHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $groupHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }
}
