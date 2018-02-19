<?php

/**
 * File containing the Functional\SearchViewTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

class SearchViewTest extends RESTFunctionalTestCase
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
     * @dataProvider xmlProvider
     * Covers POST with ContentQuery Logic on /api/ezp/v2/views.
     */
    public function testSimpleContentQuery(string $xmlQueryBody, int $expectedCount)
    {
        $request = $this->createHttpRequest('POST', '/api/ezp/v2/views', 'ViewInput+xml; version=1.1', 'ContentInfo+json');
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ViewInput>
<identifier>your-query-id</identifier>
<public>false</public>
<ContentQuery>
  <Query>
    $xmlQueryBody
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
        self::assertEquals($expectedCount, $jsonResponse->View->Result->count);
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
    <ParentLocation href="/api/ezp/v2/content/locations/1" />
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

    public function xmlProvider()
    {
        $fooTag = $this->buildFieldXml('tags', Operator::CONTAINS, 'foo');
        $barTag = $this->buildFieldXml('tags', Operator::CONTAINS, 'bar');
        $bazTag = $this->buildFieldXml('tags', Operator::CONTAINS, 'baz');
        $foobazTag = $this->buildFieldXml('tags', Operator::CONTAINS, 'foobaz');

        return [
            [
                $this->getXmlString(
                    $this->wrapIn('AND', [$fooTag, $barTag])
                ),
                1,
            ],
            [
                $this->getXmlString(
                    $this->wrapIn('OR', [
                        $this->wrapIn('AND', [$fooTag, $barTag]),
                        $this->wrapIn('AND', [$bazTag, $foobazTag]),
                    ])
                ),
                2,
            ],
            [
                $this->getXmlString(
                    $this->wrapIn('AND', [
                        $this->wrapIn('NOT', [$fooTag]),
                        $barTag,
                    ])
                ),
                1,
            ],
        ];
    }

    /**
     * @param string $name
     * @param string $operator
     * @param string|string[] $value
     * @return \DomElement
     */
    private function buildFieldXml(string $name, string $operator, $value): \DomElement
    {
        $xml = new \DOMDocument();
        $element = $xml->createElement('Field');
        $element->appendChild(new \DOMElement('name', $name));
        $element->appendChild(new \DOMElement('operator', $operator));
        if (is_array($value)) {
            $valueWrapper = $xml->createElement('value');
            foreach ($value as $key => $singleValue) {
                $valueWrapper->appendChild(new \DOMElement('value', $singleValue));
            }
            $element->appendChild($valueWrapper);

            return $element;
        }

        $element->appendChild(new \DOMElement('value', $value));

        return $element;
    }

    /**
     * @param string $logicalOperator
     * @param \DomElement|\DomElement[] $toWrap
     * @return \DomElement
     */
    private function wrapIn(string $logicalOperator, array $toWrap): \DomElement
    {
        $xml = new \DOMDocument();
        $wrapper = $xml->createElement($logicalOperator);

        foreach ($toWrap as $key => $field) {
            $innerWrapper = $xml->createElement($logicalOperator);
            $innerWrapper->appendChild($xml->importNode($field, true));
            $wrapper->appendChild($innerWrapper);
        }

        return $wrapper;
    }

    private function getXmlString(\DomElement $simpleXMLElement): string
    {
        return $simpleXMLElement->ownerDocument->saveXML($simpleXMLElement);
    }
}
