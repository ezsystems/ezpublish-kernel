<?php

/**
 * File containing the Functional\ContentTypeTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;

class ContentTypeTest extends RESTFunctionalTestCase
{
    /**
     * Covers POST /content/typegroups.
     */
    public function testCreateContentTypeGroup()
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeGroupInput>
  <identifier>testCreateContentTypeGroup</identifier>
</ContentTypeGroupInput>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/content/typegroups',
            'ContentTypeGroupInput+xml',
            'ContentTypeGroup+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @depends testCreateContentTypeGroup
     * Covers PATCH /content/typegroups/<contentTypeGroupId>
     *
     * @return string the updated content type href
     */
    public function testUpdateContentTypeGroup($contentTypeGroupHref)
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeGroupInput>
  <identifier>testUpdateContentTypeGroup</identifier>
</ContentTypeGroupInput>
XML;

        $request = $this->createHttpRequest(
            'PATCH',
            $contentTypeGroupHref,
            'ContentTypeGroupInput+xml',
            'ContentTypeGroup+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);

        return $contentTypeGroupHref;
    }

    /**
     * @depends testCreateContentTypeGroup
     * @returns string The created content type href
     * Covers POST /content/typegroups/<contentTypeGroupId>/types?publish=true
     *
     * @todo write test with full workflow (draft, edit, publish)
     */
    public function testCreateContentType($contentTypeGroupHref)
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeCreate>
  <identifier>tCreate</identifier>
  <names>
    <value languageCode="eng-GB">testCreateContentType</value>
  </names>
  <remoteId>testCreateContentType</remoteId>
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
            'ContentType+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $this->addCreatedElement($response->getHeader('Location')[0]);

        return $response->getHeader('Location')[0];
    }

    /**
     * @depends testCreateContentTypeGroup
     * Covers GET /content/typegroups/<contentTypeGroupId>
     *
     * @param string $contentTypeGroupHref
     */
    public function testListContentTypesForGroup($contentTypeGroupHref)
    {
        $response = $this->sendHttpRequest(
            $request = $this->createHttpRequest('GET', "$contentTypeGroupHref/types")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /content/typegroups.
     */
    public function testLoadContentTypeGroupList()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/content/typegroups')
        );
        self::assertHttpResponseCodeEquals($response, 200);

        // @todo test data
    }

    /**
     * @depends testUpdateContentTypeGroup
     * Covers GET /content/typegroups?identifier=<contentTypeGroupIdentifier>
     */
    public function testLoadContentTypeGroupListWithIdentifier()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/content/typegroups?identifier=testUpdateContentTypeGroup')
        );
        // @todo Check if list filtered by identifier is supposed to send a 307
        self::assertHttpResponseCodeEquals($response, 307);
    }

    /**
     * @depends testUpdateContentTypeGroup
     * Covers GET /content/typegroups/<contentTypeGroupId>
     *
     * @param string $contentTypeGroupHref
     */
    public function testLoadContentTypeGroup($contentTypeGroupHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $contentTypeGroupHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testUpdateContentTypeGroup
     * Covers GET /content/typegroups/<contentTypeGroupId>
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
     * Covers GET /content/types/<contentTypeId>
     */
    public function testLoadContentType($contentTypeHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $contentTypeHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateContentType
     * Covers GET /content/types/<contentTypeId>
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
     * Covers GET /content/types
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
     * Covers GET /content/types?identifier=<contentTypeIdentifier>
     */
    public function testListContentTypesByIdentifier()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/content/types?identifier=tCreate')
        );

        // @todo This isn't consistent with the behaviour of /content/typegroups?identifier=
        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateContentType
     * Covers GET /content/types?remoteid=<contentTypeRemoteId>
     */
    public function testListContentTypesByRemoteId()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/content/types?remoteId=testCreateContentType')
        );

        // @todo This isn't consistent with the behaviour of /content/typegroups?identifier=
        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateContentType
     * Covers COPY /content/types/<contentTypeId>
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

        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        return $href;

        // @todo test identifier (copy_of_<originalBaseIdentifier>_<newTypeId>)
    }

    /**
     * Covers POST /content/type/<contentTypeId>.
     * @depends testCopyContentType
     *
     * @return string the created content type draft href
     */
    public function testCreateContentTypeDraft($contentTypeHref)
    {
        $content = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeUpdate>
  <names>
    <value languageCode="eng-GB">testCreateContentTypeDraft</value>
  </names>
</ContentTypeUpdate>
XML;

        $request = $this->createHttpRequest(
            'POST',
            $contentTypeHref,
            'ContentTypeUpdate+xml',
            'ContentTypeInfo+json',
            $content
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @depends testCreateContentTypeDraft
     * Covers GET /content/types/<contentTypeId>/draft
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
     * Covers PATCH /content/types/<contentTypeId>/draft
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

        $request = $this->createHttpRequest(
            'PATCH',
            $contentTypeDraftHref,
            'ContentTypeUpdate+xml',
            'ContentTypeInfo+json',
            $content
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers POST /content/types/<contentTypeId>/draft/fielddefinitions.
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
            'FieldDefinition+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        return $response->getHeader('Location')[0];
    }

    /**
     * @depends testCreateContentType
     * Covers GET /content/types/<contentTypeId>/fieldDefinitions
     *
     * @return string the href of the first field definition in the list
     */
    public function testContentTypeLoadFieldDefinitionList($contentTypeHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$contentTypeHref/fieldDefinitions", '', 'FieldDefinitionList+json')
        );

        self::assertHttpResponseCodeEquals($response, 200);

        $data = json_decode($response->getBody(), true);

        return $data['FieldDefinitions']['FieldDefinition'][0]['_href'];
    }

    /**
     * @depends testAddContentTypeDraftFieldDefinition
     * Covers GET /content/types/<contentTypeId>/fieldDefinitions/<fieldDefinitionId>
     *
     * @param string $fieldDefinitionHref
     *
     * @throws \Psr\Http\Client\ClientException
     */
    public function testLoadContentTypeFieldDefinition(string $fieldDefinitionHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $fieldDefinitionHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testAddContentTypeDraftFieldDefinition
     * Covers PATCH /content/types/<contentTypeId>/fieldDefinitions/<fieldDefinitionId>
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
            'FieldDefinition+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers DELETE /content/types/<contentTypeId>/draft/fieldDefinitions/<fieldDefinitionId>.
     * @depends testAddContentTypeDraftFieldDefinition
     *
     * @param string $fieldDefinitionHref
     */
    public function deleteContentTypeDraftFieldDefinition(string $fieldDefinitionHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $fieldDefinitionHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * Covers DELETE /content/types/<contentTypeId>/draft.
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
     * Covers PUBLISH /content/types/<contentTypeId>/draft
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
     * Covers GET /content/types/<contentTypeId>/groups
     */
    public function testLoadGroupsOfContentType($contentTypeHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$contentTypeHref/groups", '', 'ContentTypeGroupRefList+json')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateContentType
     * Covers POST /content/types/<contentTypeId>/groups
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
     * Covers DELETE /content/types/{contentTypeId}/groups/{contentTypeGroupId}
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
     * Covers DELETE /content/typegroups/<contentTypeGroupId>
     */
    public function testDeleteContentTypeGroupNotEmpty($contentTypeGroupHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $contentTypeGroupHref)
        );

        self::assertHttpResponseCodeEquals($response, 403);
    }
}
