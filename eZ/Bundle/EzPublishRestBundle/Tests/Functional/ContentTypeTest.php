<?php

/**
 * File containing the Functional\ContentTypeTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use Buzz\Message\Response;
use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\Core\REST\Server\Values\ContentTypeGroupList;
use eZ\Publish\Core\REST\Server\Values\ContentTypeInfoList;

class ContentTypeTest extends RESTFunctionalTestCase
{
    private static $createdContentTypeRemoteId;
    private static $createdContentTypeIdentifier;
    private static $updatedContentTypeGroupIdentifier;

    /**
     * @covers POST /content/typegroups
     */
    public function testCreateContentTypeGroup()
    {
        $identifier = uniqid('test');
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeGroupInput>
  <identifier>$identifier</identifier>
</ContentTypeGroupInput>
XML;
        $request = $this->createHttpRequest('POST', '/api/ezp/v2/content/typegroups', 'ContentTypeGroupInput+xml', 'ContentTypeGroup+json');
        $request->setContent($body);
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location');
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @depends testCreateContentTypeGroup
     * @covers PATCH /content/typegroups/<contentTypeGroupId>
     *
     * @return string the updated content type href
     */
    public function testUpdateContentTypeGroup($contentTypeGroupHref)
    {
        $identifier = uniqid('test');
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeGroupInput>
  <identifier>$identifier</identifier>
</ContentTypeGroupInput>
XML;

        $request = $this->createHttpRequest('PATCH', $contentTypeGroupHref, 'ContentTypeGroupInput+xml', 'ContentTypeGroup+json');
        $request->setContent($body);
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);

        self::$updatedContentTypeGroupIdentifier = $identifier;

        return $contentTypeGroupHref;
    }

    /**
     * @depends testCreateContentTypeGroup
     * @returns string The created content type href
     * @covers POST /content/typegroups/<contentTypeGroupId>/types?publish=true
     *
     * @todo write test with full workflow (draft, edit, publish)
     */
    public function testCreateContentType($contentTypeGroupHref)
    {
        $identifier = uniqid('test');
        $remoteId = md5($identifier);
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeCreate>
  <identifier>$identifier</identifier>
  <names>
    <value languageCode="eng-GB">$identifier</value>
  </names>
  <remoteId>$remoteId</remoteId>
  <urlAliasSchema>&lt;title&gt;</urlAliasSchema>
  <nameSchema>&lt;title&gt;</nameSchema>
  <isContainer>true</isContainer>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <defaultAlwaysAvailable>true</defaultAlwaysAvailable>
  <defaultSortField>PATH</defaultSortField>
  <defaultSortOrder>ASC</defaultSortOrder>
  <FieldDefinitions>
    <FieldDefinition>
      <identifier>title</identifier>
      <fieldType>ezstring</fieldType>
      <fieldGroup>content</fieldGroup>
      <position>1</position>
      <isTranslatable>true</isTranslatable>
      <isRequired>true</isRequired>
      <isInfoCollector>false</isInfoCollector>
      <defaultValue>New Title</defaultValue>
      <isSearchable>true</isSearchable>
      <names>
        <value languageCode="eng-GB">Title</value>
      </names>
      <descriptions>
        <value languageCode="eng-GB">This is the title</value>
      </descriptions>
    </FieldDefinition>
   </FieldDefinitions>
</ContentTypeCreate>
XML;

        $request = $this->createHttpRequest(
            'POST',
            "$contentTypeGroupHref/types?publish=true",
            'ContentTypeCreate+xml',
            'ContentType+json'
        );
        $request->setContent($body);
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $this->addCreatedElement($response->getHeader('Location'));

        self::$createdContentTypeIdentifier = $identifier;
        self::$createdContentTypeRemoteId = $remoteId;

        return $response->getHeader('Location');
    }

    /**
     * @depends testCreateContentTypeGroup
     * @covers GET /content/typegroups/<contentTypeGroupId>
     *
     * @param string $contentTypeGroupHref
     */
    public function testListContentTypesForGroup($contentTypeGroupHref)
    {
        $response = $this->sendHttpRequest(
            $request = $this->createHttpRequest('GET', "$contentTypeGroupHref/types", '', 'ContentTypeInfoList+json')
        );

        self::assertHttpResponseCodeEquals($response, 200);

        $contentTypeInfoList = $this->parseContentTypeInfoListFromResponse($response);

        $this->assertHttpResponseHasCacheTags(
            $response,
            array_merge(
                ['content-type-group-' . $this->extractLastIdFromHref($contentTypeGroupHref)],
                array_map(
                    function (ContentType $contentType) {
                        return 'content-type-' . $contentType->id;
                    },
                    $contentTypeInfoList->contentTypes
                )
            )
        );
    }

    /**
     * @covers GET /content/typegroups
     */
    public function testLoadContentTypeGroupList()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/content/typegroups', '', 'ContentTypeGroupList+json')
        );
        self::assertHttpResponseCodeEquals($response, 200);

        $contentTypeGroupList = $this->parseContentTypeGroupListFromResponse($response);

        $this->assertHttpResponseHasCacheTags(
            $response,
            array_map(
                function (ContentTypeGroup $contentTypeGroup) {
                    return 'content-type-group-' . $contentTypeGroup->id;
                },
                $contentTypeGroupList->contentTypeGroups
            )
        );

    }

    /**
     * @depends testUpdateContentTypeGroup
     * @covers GET /content/typegroups?identifier=<contentTypeGroupIdentifier>
     */
    public function testLoadContentTypeGroupListWithIdentifier()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/content/typegroups?identifier=' . self::$updatedContentTypeGroupIdentifier)
        );
        // @todo Check if list filtered by identifier is supposed to send a 307
        self::assertHttpResponseCodeEquals($response, 307);
    }

    /**
     * @depends testUpdateContentTypeGroup
     * @covers GET /content/typegroups/<contentTypeGroupId>
     *
     * @param string $contentTypeGroupHref
     */
    public function testLoadContentTypeGroup($contentTypeGroupHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $contentTypeGroupHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);

        $contentTypeGroup = $this->parseContentTypeGroupFromResponse($response);
    }

    /**
     * @depends testUpdateContentTypeGroup
     * @covers GET /content/typegroups/<contentTypeGroupId>
     *
     * @param string $contentTypeGroupHref
     */
    public function testLoadContentTypeGroupNotFound($contentTypeGroupHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "{$contentTypeGroupHref}1234")
        );

        self::assertHttpResponseCodeEquals($response, 404);
    }

    /**
     * @depends testCreateContentType
     * @covers GET /content/types/<contentTypeId>
     */
    public function testLoadContentType($contentTypeHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $contentTypeHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);

        $contentType = $this->parseContentTypeFromResponse($response);
    }

    /**
     * @depends testCreateContentType
     * @covers GET /content/types/<contentTypeId>
     */
    public function testLoadContentTypeNotFound($contentTypeHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "{$contentTypeHref}1234")
        );

        self::assertHttpResponseCodeEquals($response, 404);
    }

    /**
     * @depends testCreateContentType
     * @covers GET /content/types
     */
    public function testListContentTypes()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/content/types')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateContentType
     * @covers GET /content/types?identifier=<contentTypeIdentifier>
     */
    public function testListContentTypesByIdentifier()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/content/types?identifier=' . self::$createdContentTypeIdentifier)
        );

        // @todo This isn't consistent with the behaviour of /content/typegroups?identifier=
        self::assertHttpResponseCodeEquals($response, 200);

        $contentType = $this->parseContentTypeFromResponse($response);
    }

    /**
     * @depends testCreateContentType
     * @covers GET /content/types?remoteid=<contentTypeRemoteId>
     */
    public function testListContentTypesByRemoteId()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/content/types?remoteId=' . self::$createdContentTypeRemoteId)
        );

        // @todo This isn't consistent with the behaviour of /content/typegroups?identifier=
        self::assertHttpResponseCodeEquals($response, 200);

        $contentType = $this->parseContentTypeFromResponse($response);
    }

    /**
     * @depends testCreateContentType
     * @covers COPY /content/types/<contentTypeId>
     *
     * @return string The copied content type href
     */
    public function testCopyContentType($sourceContentTypeHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('COPY', $sourceContentTypeHref, '', 'ContentType+json')
        );

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location');
        $this->addCreatedElement($href);

        return $href;

        // @todo test identifier (copy_of_<sourceIdentifier)
    }

    /**
     * @covers POST /content/type/<contentTypeId>
     * @depends testCopyContentType
     *
     * @return string the created content type draft href
     */
    public function testCreateContentTypeDraft($contentTypeHref)
    {
        $identifier = uniqid('test');
        $content = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeUpdate>
  <names>
    <value languageCode="eng-GB">$identifier</value>
  </names>
</ContentTypeUpdate>
XML;

        $request = $this->createHttpRequest('POST', $contentTypeHref, 'ContentTypeUpdate+xml', 'ContentTypeInfo+json');
        $request->setContent($content);
        $response = $this->sendHttpRequest(
            $request
        );

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location');
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @depends testCreateContentTypeDraft
     * @covers GET /content/types/<contentTypeId>/draft
     */
    public function testLoadContentTypeDraft($contentTypeDraftHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $contentTypeDraftHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateContentTypeDraft
     * @covers PATCH /content/types/<contentTypeId>/draft
     */
    public function testUpdateContentTypeDraft($contentTypeDraftHref)
    {
        $content = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeUpdate>
  <names>
    <value languageCode="eng-GB">testUpdateContentTypeDraft</value>
  </names>
</ContentTypeUpdate>
XML;

        $request = $this->createHttpRequest('PATCH', $contentTypeDraftHref, 'ContentTypeUpdate+xml', 'ContentTypeInfo+json');
        $request->setContent($content);
        $response = $this->sendHttpRequest(
            $request
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @covers POST /content/types/<contentTypeId>/draft/fielddefinitions
     * @depends testCreateContentTypeDraft
     *
     * @return string The content type draft field definition href
     */
    public function testAddContentTypeDraftFieldDefinition($contentTypeDraftHref)
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<FieldDefinition>
      <identifier>secondtext</identifier>
      <fieldType>ezstring</fieldType>
      <fieldGroup>content</fieldGroup>
      <position>1</position>
      <isTranslatable>true</isTranslatable>
      <isRequired>true</isRequired>
      <isInfoCollector>false</isInfoCollector>
      <defaultValue>Second text</defaultValue>
      <isSearchable>true</isSearchable>
      <names>
        <value languageCode="eng-GB">Second text</value>
      </names>
    </FieldDefinition>
XML;

        $request = $this->createHttpRequest(
            'POST',
            "$contentTypeDraftHref/fieldDefinitions",
            'FieldDefinitionCreate+xml',
            'FieldDefinition+json'
        );
        $request->setContent($body);
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        return $response->getHeader('Location');
    }

    /**
     * @depends testCreateContentType
     * @covers GET /content/types/<contentTypeId>/fieldDefinitions
     *
     * @return string the href of the first field definition in the list
     */
    public function testContentTypeLoadFieldDefinitionList($contentTypeHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$contentTypeHref/fieldDefinitions", '', 'FieldDefinitionList+json')
        );

        self::assertHttpResponseCodeEquals($response, 200);
        $this->assertHttpResponseHasCacheTags(
            $response,
            ['content-type-' . $this->extractLastIdFromHref($contentTypeHref)]
        );

        $data = json_decode($response->getContent(), true);

        return $data['FieldDefinitions']['FieldDefinition'][0]['_href'];
    }

    /**
     * @depends testAddContentTypeDraftFieldDefinition
     * @covers GET /content/types/<contentTypeId>/fieldDefinitions/<fieldDefinitionId>
     */
    public function testLoadContentTypeFieldDefinition($fieldDefinitionHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $fieldDefinitionHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);

        // @todo Cache test. Needs the contentTypeId
    }

    /**
     * @depends testAddContentTypeDraftFieldDefinition
     * @covers PATCH /content/types/<contentTypeId>/fieldDefinitions/<fieldDefinitionId>
     *
     * @todo the spec says PUT...
     */
    public function testUpdateContentTypeDraftFieldDefinition($fieldDefinitionHref)
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<FieldDefinitionUpdate>
  <identifier>updated_secondtext</identifier>
  <names>
    <value languageCode="eng-GB">Updated second text</value>
  </names>
  <defaultValue>Updated default value</defaultValue>
</FieldDefinitionUpdate>
XML;

        $request = $this->createHttpRequest(
            'PATCH',
            $fieldDefinitionHref,
            'FieldDefinitionUpdate+xml',
            'FieldDefinition+json'
        );
        $request->setContent($body);

        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @covers DELETE /content/types/<contentTypeId>/draft/fieldDefinitions/<fieldDefinitionId>
     * @depends testAddContentTypeDraftFieldDefinition
     */
    public function deleteContentTypeDraftFieldDefinition($fieldDefinitionHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $fieldDefinitionHref)
        );

        self::testLoadContentTypeFieldDefinition($response, 204);
    }

    /**
     * @covers DELETE /content/types/<contentTypeId>/draft
     * @depends testCreateContentTypeDraft
     */
    public function testDeleteContentTypeDraft($contentTypeDraftHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $contentTypeDraftHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * @depends testCreateContentType
     * @covers PUBLISH /content/types/<contentTypeId>/draft
     */
    public function testPublishContentTypeDraft($contentTypeHref)
    {
        // we need to create a content type draft first since we deleted the previous one in testDeleteContentTypeDraft
        $contentTypeDraftHref = $this->testCreateContentTypeDraft($contentTypeHref);

        $response = $this->sendHttpRequest(
            $this->createHttpRequest('PUBLISH', $contentTypeDraftHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateContentType
     * @covers GET /content/types/<contentTypeId>/groups
     */
    public function testLoadGroupsOfContentType($contentTypeHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$contentTypeHref/groups", '', 'ContentTypeGroupRefList+json')
        );

        self::assertHttpResponseCodeEquals($response, 200);

        $this->assertHttpResponseHasCacheTags(
            $response,
            [
                'content-type-' . $this->extractLastIdFromHref($contentTypeHref),
            ]
        );
    }

    /**
     * @depends testCreateContentType
     * @covers POST /content/types/<contentTypeId>/groups
     *
     * @return string the content type href
     */
    public function testLinkContentTypeToGroup($contentTypeHref)
    {
        // @todo Spec example is invalid, missing parameter name
        $request = $this->createHttpRequest('POST', "$contentTypeHref/groups?group=/api/ezp/v2/content/typegroups/1");
        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseCodeEquals($response, 200);

        return $contentTypeHref;
    }

    /**
     * @depends testLinkContentTypeToGroup
     * @covers DELETE /content/types/{contentTypeId}/groups/{contentTypeGroupId}
     */
    public function testUnlinkContentTypeFromGroup($contentTypeHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', "$contentTypeHref/groups/1")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateContentType
     */
    public function testDeleteContentType($contentTypeHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $contentTypeHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * @depends testCreateContentTypeGroup
     * @covers DELETE /content/typegroups/<contentTypeGroupId>
     */
    public function testDeleteContentTypeGroupNotEmpty($contentTypeGroupHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $contentTypeGroupHref)
        );

        self::assertHttpResponseCodeEquals($response, 403);
    }

    /**
     * @param $response
     * @return ContentTypeGroupList
     */
    private function parseContentTypeGroupListFromResponse($response): ContentTypeGroupList
    {
        $struct = json_decode($response->getContent(), true);
        $contentTypeGroupList = new ContentTypeGroupList(
            array_map(
                function (array $row) {
                    return new ContentTypeGroup(
                        [
                            'id' => $row['id'],
                        ]
                    );
                },
                $struct['ContentTypeGroupList']['ContentTypeGroup']
            ),
            ''
        );
        return $contentTypeGroupList;
    }

    /**
     * @param $response
     * @return ContentTypeInfoList
     */
    private function parseContentTypeInfoListFromResponse($response): ContentTypeInfoList
    {
        $struct = json_decode($response->getContent(), true);
        $contentTypeInfoList = new ContentTypeInfoList(
            array_map(
                function (array $row) {
                    return new ContentType(
                        [
                            'id' => $row['id'],
                            'identifier' => $row['identifier'],
                            'fieldDefinitions' => []
                        ]
                    );
                },
                $struct['ContentTypeInfoList']['ContentType']
            ),
            ''
        );
        return $contentTypeInfoList;
    }

    /**
     * @param Response $response
     * @return ContentTypeGroup
     */
    private function parseContentTypeGroupFromResponse(Response $response)
    {
        $struct = json_decode($response->getContent(), true);

        return new ContentTypeGroup(
            [
                'id' => $struct['ContentTypeGroup']['id'],
                'identifier' => $struct['ContentTypeGroup']['identifier']
            ]
        );
    }

    /**
     * @param Response $response
     * @return ContentType
     */
    private function parseContentTypeFromResponse(Response $response)
    {
        $struct = json_decode($response->getContent(), true);

        return new ContentType(
            [
                'id' => $struct['ContentType']['id'],
                'identifier' => $struct['ContentType']['identifier'],
                'fieldDefinitions' => []
            ]
        );
    }
}
