<?php

/**
 * File containing the Functional\RoleTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;
use eZ\Publish\API\Repository\Values\User\Limitation;

class RoleTest extends RESTFunctionalTestCase
{
    /**
     * @covers POST /user/roles
     *
     * BC compatible mode, will return a role
     *
     * @return string The created role href
     */
    public function testCreateRole()
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<RoleInput>
  <identifier>testCreateRole</identifier>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <names>
    <value languageCode="eng-GB">testCreateRole</value>
  </names>
  <descriptions>
    <value languageCode="eng-GB">testCreateRole description</value>
  </descriptions>
</RoleInput>
XML;
        $request = $this->createHttpRequest('POST', '/api/ezp/v2/user/roles', 'RoleInput+xml', 'Role+json');
        $request->setContent($xml);
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location');
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @covers POST /user/roles
     *
     * BC incompatible mode, will return a role draft
     *
     * @return string The created role draft href
     */
    public function testCreateRoleWithDraft()
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<RoleInput>
  <identifier>testCreateRoleDraft</identifier>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <names>
    <value languageCode="eng-GB">testCreateRoleDraft</value>
  </names>
  <descriptions>
    <value languageCode="eng-GB">testCreateRoleDraft description</value>
  </descriptions>
</RoleInput>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/user/roles?publish=false',
            'RoleInput+xml',
            'RoleDraft+json'
        );
        $request->setContent($xml);
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location');
        $this->addCreatedElement($href);

        return $href . '/draft';
    }

    /**
     * @covers GET /user/roles
     */
    public function testListRoles()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/user/roles')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateRole
     * @covers GET /user/roles/{roleId}
     */
    public function testLoadRole($roleHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $roleHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateRole
     * @covers POST /user/roles/{roleId}
     *
     * @return string The created role draft href
     */
    public function testCreateRoleDraft($roleHref)
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<RoleInput>
  <identifier>testCreateRoleDraft</identifier>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <names>
    <value languageCode="eng-GB">testCreateRoleDraft</value>
  </names>
  <descriptions>
    <value languageCode="eng-GB">testCreateRoleDraft description</value>
  </descriptions>
</RoleInput>
XML;
        $request = $this->createHttpRequest(
            'POST',
            $roleHref,
            'RoleInput+xml',
            'RoleDraft+json'
        );
        $request->setContent($xml);
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location');
        $this->addCreatedElement($href);

        return $href . '/draft';
    }

    /**
     * @depends testCreateRoleDraft
     * @covers GET /user/roles/{roleId}/draft
     */
    public function testLoadRoleDraft($roleDraftHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $roleDraftHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateRole
     * @covers PATCH /user/roles/{roleId}
     */
    public function testUpdateRole($roleHref)
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<RoleInput>
  <identifier>testUpdateRole</identifier>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <names>
    <value languageCode="eng-GB">testUpdateRole</value>
  </names>
  <descriptions>
    <value languageCode="eng-GB">testUpdateRole description</value>
  </descriptions>
</RoleInput>
XML;

        $request = $this->createHttpRequest('PATCH', $roleHref, 'RoleInput+xml', 'Role+json');
        $request->setContent($xml);
        $response = $this->sendHttpRequest($request);

        // @todo Fix failure Notice: Trying to get property of non-object in \/home\/bertrand\/www\/ezpublish-kernel\/eZ\/Publish\/Core\/Persistence\/Cache\/UserHandler.php line 174
        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateRoleDraft
     * @covers PATCH /user/roles/{roleId}/draft
     */
    public function testUpdateRoleDraft($roleDraftHref)
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<RoleInput>
  <identifier>testUpdateRoleDraft</identifier>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <names>
    <value languageCode="eng-GB">testUpdateRoleDraft</value>
  </names>
  <descriptions>
    <value languageCode="eng-GB">testUpdateRoleDraft description</value>
  </descriptions>
</RoleInput>
XML;

        $request = $this->createHttpRequest('PATCH', $roleDraftHref, 'RoleInput+xml', 'RoleDraft+json');
        $request->setContent($xml);
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @covers POST /user/roles/{roleId}/policies
     * @depends testCreateRole
     *
     * @return string The created policy href
     */
    public function testAddPolicy($roleHref)
    {
        // @todo Error in Resource URL in spec @ https://github.com/ezsystems/ezpublish-kernel/blob/master/doc/specifications/rest/REST-API-V2.rst#151213create-policy
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<PolicyCreate>
  <module>content</module>
  <function>create</function>
  <limitations>
    <limitation identifier="Class">
      <values>
        <ref href="2"/>
      </values>
    </limitation>
  </limitations>
</PolicyCreate>
XML;
        $request = $this->createHttpRequest('POST', "$roleHref/policies", 'PolicyCreate+xml', 'Policy+json');
        $request->setContent($xml);

        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location');
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @covers POST /user/roles/{roleId}/policies
     * @depends testCreateRoleDraft
     *
     * @return string The created policy href
     */
    public function testAddPolicyByRoleDraft($roleDraftHref)
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<PolicyCreate>
  <module>content</module>
  <function>create</function>
  <limitations>
    <limitation identifier="Class">
      <values>
        <ref href="1"/>
      </values>
    </limitation>
  </limitations>
</PolicyCreate>
XML;
        $request = $this->createHttpRequest(
            'POST',
            $this->roleDraftHrefToRoleHref($roleDraftHref) . '/policies',
            'PolicyCreate+xml',
            'Policy+json'
        );
        $request->setContent($xml);

        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location');
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @covers GET /user/roles/{roleId}/policies/{policyId}
     * @depends testAddPolicy
     */
    public function testLoadPolicy($policyHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $policyHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @covers GET /user/roles/{roleId}/policies
     * @depends testCreateRole
     */
    public function testLoadPolicies($roleHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$roleHref/policies")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @covers PATCH /user/roles/{roleId}/policies/{policyId}
     * @depends testAddPolicy
     */
    public function testUpdatePolicy($policyHref)
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<PolicyUpdate>
  <limitations>
    <limitation identifier="Class">
      <values>
        <ref href="1"/>
      </values>
    </limitation>
  </limitations>
</PolicyUpdate>
XML;

        $request = $this->createHttpRequest('PATCH', $policyHref, 'PolicyUpdate+xml', 'Policy+json');
        $request->setContent($xml);
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @covers PATCH /user/roles/{roleId}/policies/{policyId}
     * @depends testAddPolicyByRoleDraft
     */
    public function testUpdatePolicyByRoleDraft($policyHref)
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<PolicyUpdate>
  <limitations>
    <limitation identifier="Class">
      <values>
        <ref href="1"/>
      </values>
    </limitation>
  </limitations>
</PolicyUpdate>
XML;

        $request = $this->createHttpRequest('PATCH', $policyHref, 'PolicyUpdate+xml', 'Policy+json');
        $request->setContent($xml);
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateRole
     * @covers POST /user/users/{userId}/roles
     *
     * @return string assigned role href
     *
     * @todo stop using the anonymous user, this is dangerous...
     */
    public function testAssignRoleToUser($roleHref)
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<RoleAssignInput>
  <Role href="{$roleHref}" media-type="application/vnd.ez.api.RoleAssignInput+xml"/>
</RoleAssignInput>
XML;

        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/user/users/10/roles',
            'RoleAssignInput+xml',
            'RoleAssignmentList+json'
        );
        $request->setContent($xml);

        $response = $this->sendHttpRequest($request);
        $roleAssignmentArray = json_decode($response->getContent(), true);

        self::assertHttpResponseCodeEquals($response, 200);

        return $roleAssignmentArray['RoleAssignmentList']['RoleAssignment'][0]['_href'];
    }

    /**
     * @covers       POST /user/users/{userId}/roles
     *
     * @param string $roleHref
     * @param array $limitation
     *
     * @return string assigned role href
     * @dataProvider provideLimitations
     */
    public function testAssignRoleToUserWithLimitation(array $limitation)
    {
        $roleHref = $this->createAndPublishRole(__METHOD__ . '_' . $limitation['identifier']);

        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<RoleAssignInput>
  <Role href="{$roleHref}" media-type="application/vnd.ez.api.RoleAssignInput+xml"/>
  <limitation identifier="{$limitation['identifier']}">
      <values>
          <ref href="{$limitation['href']}" media-type="application/vnd.ez.api.{$limitation['identifier']}+xml" />
      </values>
  </limitation>
</RoleAssignInput>
XML;

        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/user/users/10/roles',
            'RoleAssignInput+xml',
            'RoleAssignmentList+json'
        );
        $request->setContent($xml);

        $response = $this->sendHttpRequest($request);
        $roleAssignmentArray = json_decode($response->getContent(), true);

        self::assertHttpResponseCodeEquals($response, 200);

        return $roleAssignmentArray['RoleAssignmentList']['RoleAssignment'][0]['_href'];
    }

    public function provideLimitations()
    {
        return [
            [['identifier' => 'Section', 'href' => '/api/ezp/v2/content/sections/1']],
            [['identifier' => 'Subtree', 'href' => '/api/ezp/v2/content/locations/1/2/']],
        ];
    }

    /**
     * @covers GET /user/users/{userId}/roles/{roleId}
     * @depends testAssignRoleToUser
     */
    public function testLoadRoleAssignmentForUser($roleAssignmentHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $roleAssignmentHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @covers DELETE /user/users/{userId}/roles/{roleId}
     * @depends testAssignRoleToUser
     */
    public function testUnassignRoleFromUser($roleAssignmentHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $roleAssignmentHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateRole
     * @covers POST /user/groups/{groupId}/roles
     *
     * @return string role assignment href
     */
    public function testAssignRoleToUserGroup($roleHref)
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<RoleAssignInput>
  <Role href="{$roleHref}" media-type="application/vnd.ez.api.RoleAssignInput+xml"/>
  <limitation identifier="Section">
      <values>
          <ref href="/api/ezp/v2/content/sections/1" media-type="application/vnd.ez.api.Section+xml" />
      </values>
  </limitation>
</RoleAssignInput>
XML;

        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/user/groups/1/5/44/roles',
            'RoleAssignInput+xml',
            'RoleAssignmentList+json'
        );
        $request->setContent($xml);

        $response = $this->sendHttpRequest($request);
        $roleAssignmentArray = json_decode($response->getContent(), true);

        self::assertHttpResponseCodeEquals($response, 200);

        return $roleAssignmentArray['RoleAssignmentList']['RoleAssignment'][0]['_href'];
    }

    /**
     * @covers GET /user/groups/{groupId}/roles/{roleId}
     * @depends testAssignRoleToUserGroup
     */
    public function testLoadRoleAssignmentForUserGroup($roleAssignmentHref)
    {
        $response = $this->sendHttpRequest(
            $request = $this->createHttpRequest('GET', $roleAssignmentHref)
        );

        self::markTestIncomplete('Requires that visitors are fixed (group url generation)');
        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @covers DELETE /user/groups/{groupId}/roles/{roleId}
     * @depends testAssignRoleToUserGroup
     */
    public function testUnassignRoleFromUserGroup($roleAssignmentHref)
    {
        $response = $this->sendHttpRequest(
            $request = $this->createHttpRequest('DELETE', $roleAssignmentHref)
        );

        self::markTestIncomplete('Requires that visitors are fixed (group url generation)');
        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @covers GET /user/users/{userId}/roles
     */
    public function testLoadRoleAssignmentsForUser()
    {
        $response = $this->sendHttpRequest(
            $request = $this->createHttpRequest('GET', '/api/ezp/v2/user/users/10/roles')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @covers GET /user/groups/{groupPath}/roles
     */
    public function testLoadRoleAssignmentsForUserGroup()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/user/groups/1/5/44/roles')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @covers GET /user/policies?userId={userId}
     */
    public function testListPoliciesForUser()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/user/policies?userId=10')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @covers DELETE /user/roles/{roleId}/policies/{policyId}
     * @depends testAddPolicy
     */
    public function testDeletePolicy($policyHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $policyHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * @covers DELETE /user/roles/{roleId}/policies/{policyId}
     * @depends testAddPolicyByRoleDraft
     */
    public function testRemovePolicyByRoleDraft($policyHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $policyHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * @covers DELETE /user/roles/{roleId}/policies
     * @depends testCreateRole
     */
    public function testDeletePolicies($roleHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', "$roleHref/policies")
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * @covers DELETE /user/roles/{roleId}
     * @depends testCreateRole
     */
    public function testDeleteRole($roleHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $roleHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * @covers PUBLISH /user/roles/{roleId}/draft
     * @depends testCreateRoleDraft
     */
    public function testPublishRoleDraft($roleDraftHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('PUBLISH', $roleDraftHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
        self::assertHttpResponseHasHeader(
            $response,
            'Location',
            '/api/ezp/v2/user/roles/' . preg_replace('/.*roles\/(\d+).*/', '$1', $roleDraftHref)
        );
    }

    /**
     * @covers DELETE /user/roles/{roleId}/draft
     * @depends testCreateRoleDraft
     */
    public function testDeleteRoleDraft($roleDraftHref)
    {
        // we need to create a role draft first since we published the previous one in testPublishRoleDraft
        $roleHref = $this->testCreateRoleDraft($this->roleDraftHrefToRoleHref($roleDraftHref));

        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $roleHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * Helper method for changing a roledraft href to a role href.
     *
     * @param string $roleDraftHref Role draft href
     *
     * @return string Role href
     */
    private function roleDraftHrefToRoleHref($roleDraftHref)
    {
        return str_replace('/draft', '', $roleDraftHref);
    }

    /**
     * Creates and publishes a role with $identifier.
     *
     * @param string $identifier
     *
     * @return string The href of the published role
     */
    private function createAndPublishRole($identifier)
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<RoleInput>
  <identifier>$identifier</identifier>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <names>
    <value languageCode="eng-GB">$identifier</value>
  </names>
  <descriptions>
    <value languageCode="eng-GB">$identifier description</value>
  </descriptions>
</RoleInput>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/user/roles',
            'RoleInput+xml',
            'RoleDraft+json'
        );
        $request->setContent($xml);
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        return $response->getHeader('Location');
    }
}
