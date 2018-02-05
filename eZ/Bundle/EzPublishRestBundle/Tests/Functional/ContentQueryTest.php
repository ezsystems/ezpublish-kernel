<?php

namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;

class ContentQueryTest extends RESTFunctionalTestCase
{
    /**
     * @var string
     */
    protected $contentTypeHref;

    /**
     * @var string[]
     */
    protected $contentHrefList;

    protected function setUp()
    {
        parent::setUp();
        $this->contentTypeHref = $this->createTestContentType();
        $this->contentHrefList[] = $this->createTestContentWithTags('test-name', ['foo', 'bar']);
        $this->contentHrefList[] = $this->createTestContentWithTags('fancy-name', ['baz', 'foobaz']);
        $this->contentHrefList[] = $this->createTestContentWithTags('even-fancier', ['bar', 'bazfoo']);
    }

    protected function tearDown()
    {
        parent::tearDown();
        array_map([$this, 'deleteContent'], $this->contentHrefList);
        $this->deleteContent($this->contentTypeHref);
    }

    /**
     * Covers POST with basic ContentQuery Logic on /api/ezp/v2/views.
     */
    public function testSimpleContentQuery()
    {
        $request = $this->createHttpRequest('POST', '/api/ezp/v2/views', 'ViewInput+xml; version=1.1', 'ContentInfo+json');
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ViewInput>
<identifier>your-query-id</identifier>
<public>false</public>
<ContentQuery>
  <Query>
    <LogicalAnd>
      <Field>
        <name>tags</name> 
        <operator>CONTAINS</operator>
        <value>foo</value>
      </Field>
    </LogicalAnd>
    <LogicalAnd>
      <Field>
        <name>tags</name>
        <operator>CONTAINS</operator>
        <value>bar</value>
      </Field>
    </LogicalAnd>
  </Query>  
  <limit>10</limit>  
  <offset>0</offset> 
</ContentQuery>
</ViewInput>
XML;

        $request->setContent($body);

        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseCodeEquals($response, 200);
        $jsonResponse = json_decode($response->getContent());
        self::assertEquals(1, $jsonResponse->View->Result->count);
    }

    /**
     * Covers POST with basic ContentQuery Logic on /api/ezp/v2/views.
     */
    public function testCombinedAndWithOrContentQuery()
    {
        $request = $this->createHttpRequest('POST', '/api/ezp/v2/views', 'ViewInput+xml; version=1.1', 'ContentInfo+json');
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ViewInput>
<identifier>your-query-id</identifier>
<public>false</public>
<ContentQuery>
  <Query>
    <LogicalOr>
      <LogicalAnd>
        <Field>
          <name>tags</name> 
          <operator>CONTAINS</operator>
          <value>foo</value>
        </Field>
      </LogicalAnd>
      <LogicalAnd>
        <Field>
          <name>tags</name>
          <operator>CONTAINS</operator>
          <value>bar</value>
        </Field>
      </LogicalAnd>    
    </LogicalOr>
    <LogicalOr>
      <LogicalAnd>
        <Field>
          <name>tags</name> 
          <operator>CONTAINS</operator>
          <value>baz</value>
        </Field>
      </LogicalAnd>
      <LogicalAnd>
        <Field>
          <name>tags</name>
          <operator>CONTAINS</operator>
          <value>foobaz</value>
        </Field>
      </LogicalAnd>     
    </LogicalOr>
  </Query>  
  <limit>10</limit>  
  <offset>0</offset> 
</ContentQuery>
</ViewInput>
XML;

        $request->setContent($body);

        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseCodeEquals($response, 200);
        $jsonResponse = json_decode($response->getContent());
        self::assertEquals(2, $jsonResponse->View->Result->count);
    }

    /**
     * Covers POST with basic ContentQuery Logic on /api/ezp/v2/views.
     */
    public function testCombinedAndWithNotContentQuery()
    {
        $request = $this->createHttpRequest('POST', '/api/ezp/v2/views', 'ViewInput+xml; version=1.1', 'ContentInfo+json');
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ViewInput>
<identifier>your-query-id</identifier>
<public>false</public>
<ContentQuery>
  <Query>
    <LogicalAnd>
      <LogicalNot>
        <Field>
          <name>tags</name> 
          <operator>CONTAINS</operator>
          <value>foo</value>
        </Field>
      </LogicalNot>
    </LogicalAnd>
    <LogicalAnd>
      <Field>
        <name>tags</name> 
        <operator>CONTAINS</operator>
        <value>bar</value>
      </Field>
    </LogicalAnd>
  </Query>  
  <limit>10</limit>  
  <offset>0</offset> 
</ContentQuery>
</ViewInput>
XML;

        $request->setContent($body);

        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseCodeEquals($response, 200);
        $jsonResponse = json_decode($response->getContent());
        self::assertEquals(1, $jsonResponse->View->Result->count);
    }

    private function createTestContentType(): string
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeCreate>
  <identifier>tags-test</identifier>
  <names>
    <value languageCode="eng-GB">testContentQueryWithTags</value>
  </names>
  <remoteId>testContentQueryWithTags</remoteId>
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
    <FieldDefinition>
      <identifier>tags</identifier>
      <fieldType>ezkeyword</fieldType>
      <fieldGroup>content</fieldGroup>
      <position>2</position>
      <isTranslatable>true</isTranslatable>
      <isRequired>true</isRequired>
      <isInfoCollector>false</isInfoCollector>
      <isSearchable>true</isSearchable>
      <names>
        <value languageCode="eng-GB">Tags</value>
      </names>
      <descriptions>
        <value languageCode="eng-GB">Those are searchable tags</value>
      </descriptions>
    </FieldDefinition>
   </FieldDefinitions>
</ContentTypeCreate>
XML;

        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/content/typegroups/1/types?publish=true',
            'ContentTypeCreate+xml',
            'ContentType+json'
        );
        $request->setContent($body);
        $response = $this->sendHttpRequest($request);

        return $response->getHeader('Location');
    }

    private function createTestContentWithTags(string $name, array $tags): string
    {
        $request = $this->createHttpRequest('POST', '/api/ezp/v2/content/objects', 'ContentCreate+xml', 'ContentInfo+json');
        $tagsString = implode(',', $tags);
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentCreate>
  <ContentType href="$this->contentTypeHref" />
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <LocationCreate>
    <ParentLocation href="/api/ezp/v2/content/locations/1/2" />
    <priority>0</priority>
    <hidden>false</hidden>
    <sortField>PATH</sortField>
    <sortOrder>ASC</sortOrder>
  </LocationCreate>
  <Section href="/api/ezp/v2/content/sections/1" />
  <alwaysAvailable>true</alwaysAvailable>
  <remoteId>$name</remoteId>
  <User href="/api/ezp/v2/user/users/14" />
  <modificationDate>2018-01-30T18:30:00</modificationDate>
  <fields>
    <field>
      <fieldDefinitionIdentifier>title</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>$name</fieldValue>
    </field>
    <field>
      <fieldDefinitionIdentifier>tags</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>$tagsString</fieldValue>
    </field>
    </fields>
</ContentCreate>
XML;
        $request->setContent($body);

        $response = $this->sendHttpRequest($request);
        $href = $response->getHeader('Location');
        $this->sendHttpRequest(
            $this->createHttpRequest('PUBLISH', "$href/versions/1")
        );

        return $href;
    }

    private function deleteContent($href)
    {
        $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $href)
        );
    }
}
