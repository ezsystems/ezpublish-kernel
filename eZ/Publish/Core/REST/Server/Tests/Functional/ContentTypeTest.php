<?php
namespace eZ\Publish\Core\Server\Tests\Functional;

use eZ\Publish\Core\REST\Server\Tests\Functional\TestCase as RESTFunctionalTestCase;

class ContentTypeTest extends RESTFunctionalTestCase
{
    /**
     * @covers POST /content/typegroups
     */
    public function testCreateContentTypeGroup()
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeGroupInput>
  <identifier>testCreateContentTypeGroup</identifier>
</ContentTypeGroupInput>
XML;
        $request = $this->createHttpRequest( 'POST', '/api/ezp/v2/content/typegroups', 'ContentTypeGroupInput+xml', 'ContentTypeGroup+json' );
        $request->setContent( $body );
        $response = $this->sendHttpRequest( $request );

        self::assertHttpResponseCodeEquals( $response, 201 );
        self::assertHttpResponseHasHeader( $response, 'Location' );

        $this->addCreatedElement( $response->getHeader( 'Location' ) );
        return $response->getHeader( 'Location' );
    }

    /**
     * @depends testCreateContentTypeGroup
     * @covers PATCH /content/typegroups/<contentTypeGroupId>
     * @return string the updated content type href
     */
    public function testUpdateContentTypeGroup( $contentTypeGroupHref )
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeGroupInput>
  <identifier>testUpdateContentTypeGroup</identifier>
</ContentTypeGroupInput>
XML;

        $request = $this->createHttpRequest( 'PATCH', $contentTypeGroupHref, 'ContentTypeGroupInput+xml', 'ContentTypeGroup+json' );
        $request->setContent( $body );
        $response = $this->sendHttpRequest( $request );

        self::assertHttpResponseCodeEquals( $response, 200 );

        return $contentTypeGroupHref;
    }

    /**
     * @depends testCreateContentTypeGroup
     * @returns string The created content type href
     * @covers POST /content/typegroups/<contentTypeGroupId>/types
     */
    public function testCreateContentType( $contentTypeGroupHref )
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeCreate>
  <identifier>testCreateContentType</identifier>
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
        <value languageCode="eng-US">This is the title</value>
      </descriptions>
    </FieldDefinition>
   </FieldDefinitions>
</ContentTypeCreate>
XML;

        $request = $this->createHttpRequest(
            'POST',
            "$contentTypeGroupHref/types?publish=true",
            "ContentTypeCreate+xml",
            "ContentType+json"
        );
        $request->setContent( $body );
        $response = $this->sendHttpRequest( $request );

        self::assertHttpResponseCodeEquals( $response, 201 );
        self::assertHttpResponseHasHeader( $response, 'Location' );

        $this->addCreatedElement( $response->getHeader( 'Location' ) );
        return $response->getHeader( 'Location' );
    }

    /**
     * @depends testCreateContentTypeGroup
     * @covers GET /content/typegroups/<contentTypeGroupId>
     * @param string $contentTypeGroupHref
     */
    public function testListContentTypesForGroup( $contentTypeGroupHref )
    {
        $response = $this->sendHttpRequest(
            $request = $this->createHttpRequest( 'GET', "$contentTypeGroupHref/types" )
        );

        self::assertHttpResponseCodeEquals( $response, 200 );
    }

    /**
     * @covers GET /content/typegroups
     */
    public function testLoadContentTypeGroupList()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( "GET", "/api/ezp/v2/content/typegroups" )
        );
        self::assertHttpResponseCodeEquals( $response, 200 );

        // @todo test data
    }

    /**
     * @depends testUpdateContentTypeGroup
     * @covers GET /content/typegroups?identifier=<contentTypeGroupIdentifier>
     */
    public function testLoadContentTypeGroupListWithIdentifier()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( "GET", "/api/ezp/v2/content/typegroups?identifier=testUpdateContentTypeGroup" )
        );
        // @todo Check if list filtered by identifier is supposed to send a 307
        self::assertHttpResponseCodeEquals( $response, 307 );
    }

    /**
     * @depends testUpdateContentTypeGroup
     * @covers GET /content/typegroups/<contentTypeGroupId>
     * @param string $contentTypeGroupHref
     */
    public function testLoadContentTypeGroup( $contentTypeGroupHref )
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( "GET", $contentTypeGroupHref )
        );

        self::assertHttpResponseCodeEquals( $response, 200 );
    }

    /**
     * @depends testUpdateContentTypeGroup
     * @covers GET /content/typegroups/<contentTypeGroupId>
     * @param string $contentTypeGroupHref
     */
    public function testLoadContentTypeGroupNotFound( $contentTypeGroupHref )
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( "GET", "{$contentTypeGroupHref}testLoadContentTypeGroupNotFound" )
        );

        self::assertHttpResponseCodeEquals( $response, 404 );
    }

    /**
     * @depends testCreateContentType
     * @covers GET /content/types/<contentTypeId>
     */
    public function testLoadContentType( $contentTypeHref )
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( "GET", $contentTypeHref )
        );

        self::assertHttpResponseCodeEquals( $response, 200 );
    }

    /**
     * @depends testCreateContentType
     * @covers GET /content/types/<contentTypeId>
     */
    public function testLoadContentTypeNotFound( $contentTypeHref )
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( "GET", $contentTypeHref . __FUNCTION__ )
        );

        self::assertHttpResponseCodeEquals( $response, 404 );
    }

    /**
     * @depends testCreateContentType
     * @covers GET /content/types
     */
    public function testListContentTypes()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( "GET", "/api/ezp/v2/content/types" )
        );

        self::assertHttpResponseCodeEquals( $response, 200 );
    }

    /**
     * @depends testCreateContentType
     * @covers GET /content/types?identifier=<contentTypeIdentifier>
     */
    public function testListContentTypesByIdentifier()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( "GET", "/api/ezp/v2/content/types?identifier=testCreateContentType" )
        );

        // @todo This isn't consistent with the behaviour of /content/typegroups?identifier=
        self::assertHttpResponseCodeEquals( $response, 200 );
    }

    /**
     * @depends testCreateContentType
     * @covers GET /content/types?remoteid=<contentTypeRemoteId>
     */
    public function testListContentTypesByRemoteId()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( "GET", "/api/ezp/v2/content/types?remoteId=testCreateContentType" )
        );

        // @todo This isn't consistent with the behaviour of /content/typegroups?identifier=
        self::assertHttpResponseCodeEquals( $response, 200 );
    }

    /**
     * @depends testCreateContentType
     */
    public function testDeleteContentType( $contentTypeHref )
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( 'DELETE', $contentTypeHref )
        );

        self::assertHttpResponseCodeEquals( $response, 204 );
    }

    /**
     * @depends testCreateContentTypeGroup
     * @covers DELETE /content/typegroups/<contentTypeGroupId>
     */
    public function testDeleteContentTypeGroup( $contentTypeGroupHref )
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest( 'DELETE', $contentTypeGroupHref )
        );

        self::assertHttpResponseCodeEquals( $response, 204 );
    }
}
